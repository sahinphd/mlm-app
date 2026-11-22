<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\CreditAccount;
use App\Models\EmiSchedule;
use App\Models\Commission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmiSchedulingAndOverdueRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $parent;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->parent = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $this->customer = User::factory()->create(['role' => 'user', 'status' => 'active']);

        Wallet::create(['user_id' => $this->parent->id, 'main_balance' => 0, 'commission_balance' => 0, 'earning_balance' => 0]);
        Wallet::create(['user_id' => $this->customer->id, 'main_balance' => 0, 'commission_balance' => 0, 'earning_balance' => 0]);

        DB::table('referrals')->insert([
            ['user_id' => $this->parent->id, 'parent_id' => null, 'referral_code' => 'PAR1', 'level_depth' => 0],
            ['user_id' => $this->customer->id, 'parent_id' => $this->parent->id, 'referral_code' => 'CUST1', 'level_depth' => 1],
        ]);
    }

    public function test_overdue_emi_is_auto_settled_when_wallet_balance_is_added(): void
    {
        Storage::disk('local')->put('settings.json', json_encode([
            'site_name' => 'Duare Dokandar',
            'repurchase_commission_level_1' => 10,
            'default_emi_amount' => 250,
            'emi_frequency' => 7,
            'commission_lock_period_days' => 0,
        ]));

        $ca = CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 1000,
            'used_credit' => 500,
            'available_credit' => 500,
            'approval_status' => 'approved',
        ]);

        $order = \App\Models\Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 500,
            'total_bv' => 0,
            'paid_amount' => 0,
            'credit_amount' => 500,
            'payment_method' => 'credit_wallet',
            'status' => 'completed',
        ]);

        // Overdue EMI of 250
        $emi = EmiSchedule::create([
            'user_id' => $this->customer->id,
            'order_id' => $order->id,
            'total_amount' => 500,
            'installment_amount' => 250,
            'interval_days' => 7,
            'due_date' => Carbon::yesterday()->toDateString(),
            'status' => 'overdue',
            'commission_distributed' => false,
        ]);

        // Admin adds 300 to customer's wallet via Customer Lookup
        $response = $this->actingAs($this->admin)->postJson('/admin/customer-lookup/' . $this->customer->id . '/add-money', [
            'amount' => 300,
            'note' => 'Customer deposit',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'settled_emis' => 1,
                'settled_amount' => 250,
            ]);

        // Assert overdue EMI is now paid and commission distributed
        $emiFresh = $emi->fresh();
        $this->assertEquals('paid', $emiFresh->status);
        $this->assertTrue($emiFresh->commission_distributed);

        // Wallet balance: 300 added - 250 auto-recovered = 50 remaining
        $this->assertEquals(50, $this->customer->fresh()->wallet->main_balance);

        // Credit restored: available credit 500 + 250 = 750, used credit 500 - 250 = 250
        $caFresh = $ca->fresh();
        $this->assertEquals(750, $caFresh->available_credit);
        $this->assertEquals(250, $caFresh->used_credit);

        // Parent received 10% commission on 250 = 25
        $this->assertEquals(25, $this->parent->fresh()->wallet->commission_balance);
    }

    public function test_sequential_emi_scheduling_chains_second_order_after_first_order_emis(): void
    {
        Storage::disk('local')->put('settings.json', json_encode([
            'site_name' => 'Duare Dokandar',
            'default_emi_amount' => 500,
            'emi_frequency' => 7,
            'emi_day_anchor' => 'none',
            'emi_queue_mode' => 'sequential',
        ]));

        CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 5000,
            'used_credit' => 0,
            'available_credit' => 5000,
            'approval_status' => 'approved',
        ]);

        $mlm = app(\App\Services\MLMService::class);

        // Order 1: 1000 credit -> 2 EMIs of 500 each (+7 days, +14 days)
        $order1 = \App\Models\Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 1000,
            'total_bv' => 0,
            'paid_amount' => 0,
            'credit_amount' => 1000,
            'payment_method' => 'credit_wallet',
            'status' => 'completed',
        ]);

        $mlm->generateEmiSchedules($this->customer, $order1);

        $order1Emis = EmiSchedule::where('order_id', $order1->id)->orderBy('id')->get();
        $this->assertCount(2, $order1Emis);
        $expectedOrder1Due1 = Carbon::today()->addDays(7)->toDateString();
        $expectedOrder1Due2 = Carbon::today()->addDays(14)->toDateString();
        $this->assertEquals($expectedOrder1Due1, Carbon::parse($order1Emis[0]->due_date)->toDateString());
        $this->assertEquals($expectedOrder1Due2, Carbon::parse($order1Emis[1]->due_date)->toDateString());

        // Order 2: 500 credit -> In sequential mode, must be queued AFTER Order 1's final due date (+14 days)
        // So Order 2 EMI 1 must be due at +21 days!
        $order2 = \App\Models\Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 500,
            'total_bv' => 0,
            'paid_amount' => 0,
            'credit_amount' => 500,
            'payment_method' => 'credit_wallet',
            'status' => 'completed',
        ]);

        $mlm->generateEmiSchedules($this->customer, $order2);

        $order2Emis = EmiSchedule::where('order_id', $order2->id)->orderBy('id')->get();
        $this->assertCount(1, $order2Emis);
        $expectedOrder2Due = Carbon::today()->addDays(21)->toDateString();
        $this->assertEquals($expectedOrder2Due, Carbon::parse($order2Emis[0]->due_date)->toDateString());
    }

    public function test_concurrent_emi_scheduling_starts_immediately_when_mode_is_concurrent(): void
    {
        Storage::disk('local')->put('settings.json', json_encode([
            'site_name' => 'Duare Dokandar',
            'default_emi_amount' => 500,
            'emi_frequency' => 7,
            'emi_day_anchor' => 'none',
            'emi_queue_mode' => 'concurrent',
        ]));

        CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 5000,
            'used_credit' => 0,
            'available_credit' => 5000,
            'approval_status' => 'approved',
        ]);

        $mlm = app(\App\Services\MLMService::class);

        // Order 1: 500 credit
        $order1 = \App\Models\Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 500,
            'total_bv' => 0,
            'paid_amount' => 0,
            'credit_amount' => 500,
            'payment_method' => 'credit_wallet',
            'status' => 'completed',
        ]);
        $mlm->generateEmiSchedules($this->customer, $order1);

        // Order 2: 500 credit in concurrent mode starts immediately (+7 days)
        $order2 = \App\Models\Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 500,
            'total_bv' => 0,
            'paid_amount' => 0,
            'credit_amount' => 500,
            'payment_method' => 'credit_wallet',
            'status' => 'completed',
        ]);
        $mlm->generateEmiSchedules($this->customer, $order2);

        $order2Emi = EmiSchedule::where('order_id', $order2->id)->first();
        $expectedDue = Carbon::today()->addDays(7)->toDateString();
        $this->assertEquals($expectedDue, Carbon::parse($order2Emi->due_date)->toDateString());
    }

    public function test_day_anchor_schedules_emis_on_specific_weekday(): void
    {
        Storage::disk('local')->put('settings.json', json_encode([
            'site_name' => 'Duare Dokandar',
            'default_emi_amount' => 500,
            'emi_frequency' => 7,
            'emi_day_anchor' => 'friday',
            'emi_queue_mode' => 'sequential',
        ]));

        CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 5000,
            'used_credit' => 0,
            'available_credit' => 5000,
            'approval_status' => 'approved',
        ]);

        $mlm = app(\App\Services\MLMService::class);

        $order = \App\Models\Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 1500,
            'total_bv' => 0,
            'paid_amount' => 0,
            'credit_amount' => 1500,
            'payment_method' => 'credit_wallet',
            'status' => 'completed',
        ]);

        $mlm->generateEmiSchedules($this->customer, $order);

        $emis = EmiSchedule::where('order_id', $order->id)->orderBy('id')->get();
        $this->assertCount(3, $emis);

        foreach ($emis as $emi) {
            $carbonDate = Carbon::parse($emi->due_date);
            $this->assertEquals('Friday', $carbonDate->englishDayOfWeek, "EMI #{$emi->id} should fall on a Friday");
        }

        // Each subsequent EMI is exactly 7 days after the previous
        $this->assertEquals(7, Carbon::parse($emis[0]->due_date)->diffInDays(Carbon::parse($emis[1]->due_date)));
        $this->assertEquals(7, Carbon::parse($emis[1]->due_date)->diffInDays(Carbon::parse($emis[2]->due_date)));
    }

    public function test_custom_interval_15_days_schedules_at_15_day_increments(): void
    {
        Storage::disk('local')->put('settings.json', json_encode([
            'site_name' => 'Duare Dokandar',
            'default_emi_amount' => 500,
            'emi_frequency' => 15,
            'emi_day_anchor' => 'none',
            'emi_queue_mode' => 'sequential',
        ]));

        CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 5000,
            'used_credit' => 0,
            'available_credit' => 5000,
            'approval_status' => 'approved',
        ]);

        $mlm = app(\App\Services\MLMService::class);

        $order = \App\Models\Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 1000,
            'total_bv' => 0,
            'paid_amount' => 0,
            'credit_amount' => 1000,
            'payment_method' => 'credit_wallet',
            'status' => 'completed',
        ]);

        $mlm->generateEmiSchedules($this->customer, $order);

        $emis = EmiSchedule::where('order_id', $order->id)->orderBy('id')->get();
        $this->assertCount(2, $emis);

        $this->assertEquals(Carbon::today()->addDays(15)->toDateString(), Carbon::parse($emis[0]->due_date)->toDateString());
        $this->assertEquals(Carbon::today()->addDays(30)->toDateString(), Carbon::parse($emis[1]->due_date)->toDateString());
    }
}
