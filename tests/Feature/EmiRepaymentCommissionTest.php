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
use App\Models\BvCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmiRepaymentCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::disk('local')->put('settings.json', json_encode([
            'site_name' => 'Duare Dokandar',
            'repurchase_commission_level_1' => 10, // 10%
            'repurchase_commission_level_2' => 5,  // 5%
            'repurchase_commission_level_3' => 2,
            'repurchase_commission_level_4' => 1,
            'repurchase_commission_level_5' => 1,
            'order_commission_level_1' => 2.0,     // 2 INR per BV
            'order_commission_level_2' => 1.0,     // 1 INR per BV
            'default_emi_amount' => 500,
            'emi_frequency' => 7,
            'late_penalty_amount' => 50,
            'commission_lock_period_days' => 0,
        ]));
    }

    public function test_pure_credit_purchase_defers_commission_until_emi_repayment(): void
    {
        // Setup hierarchy: Grandparent -> Parent -> Buyer
        $grand = User::factory()->create(['status' => 'active', 'role' => 'user']);
        $parent = User::factory()->create(['status' => 'active', 'role' => 'user']);
        $buyer = User::factory()->create(['status' => 'active', 'role' => 'user']);

        Wallet::create(['user_id' => $grand->id, 'main_balance' => 0, 'commission_balance' => 0, 'earning_balance' => 0]);
        Wallet::create(['user_id' => $parent->id, 'main_balance' => 0, 'commission_balance' => 0, 'earning_balance' => 0]);
        $buyerWallet = Wallet::create(['user_id' => $buyer->id, 'main_balance' => 1000, 'commission_balance' => 0, 'earning_balance' => 0]);

        DB::table('referrals')->insert([
            ['user_id' => $grand->id, 'parent_id' => null, 'referral_code' => 'G1', 'level_depth' => 0],
            ['user_id' => $parent->id, 'parent_id' => $grand->id, 'referral_code' => 'P1', 'level_depth' => 1],
            ['user_id' => $buyer->id, 'parent_id' => $parent->id, 'referral_code' => 'B1', 'level_depth' => 2],
        ]);

        $product = Product::create(['name' => 'Credit Item', 'price' => 1000, 'stock' => 10, 'bv' => 10]);

        CreditAccount::create([
            'user_id' => $buyer->id,
            'credit_limit' => 2000,
            'used_credit' => 0,
            'available_credit' => 2000,
            'approval_status' => 'approved',
        ]);

        // Place 100% credit order
        $response = $this->actingAs($buyer)->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'credit_wallet',
        ]);

        $response->assertStatus(200);

        $order = \App\Models\Order::where('user_id', $buyer->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(0, $order->paid_amount);
        $this->assertEquals(1000, $order->credit_amount);

        // At order placement, 0 commissions should be distributed
        $this->assertEquals(0, Commission::where('order_id', $order->id)->count());
        $this->assertEquals(0, BvCommission::where('order_id', $order->id)->count());
        $this->assertEquals(0, $parent->fresh()->wallet->commission_balance);
        $this->assertEquals(0, $grand->fresh()->wallet->commission_balance);

        // 2 EMIs generated (500 + 500)
        $emis = EmiSchedule::where('order_id', $order->id)->orderBy('id')->get();
        $this->assertCount(2, $emis);
        $emi1 = $emis[0];
        $emi2 = $emis[1];

        // Buyer pays EMI 1 (500 INR)
        $payResp = $this->actingAs($buyer)->post('/credit/emis/' . $emi1->id . '/pay');
        $payResp->assertSessionHas('success');

        $emi1Fresh = $emi1->fresh();
        $this->assertEquals('paid', $emi1Fresh->status);
        $this->assertTrue($emi1Fresh->commission_distributed);

        // Parent should receive 10% of 500 = 50 INR repurchase commission
        // Grandparent should receive 5% of 500 = 25 INR repurchase commission
        // BV for EMI 1 = 10 BV * (500 / 1000) = 5 BV
        // Parent BV commission = 5 BV * 2.0 rate = 10 INR
        // Grand BV commission = 5 BV * 1.0 rate = 5 INR

        $this->assertDatabaseHas('commissions', [
            'user_id' => $parent->id,
            'order_id' => $order->id,
            'level' => 1,
            'amount' => 50,
            'type' => 'repurchase',
        ]);
        $this->assertDatabaseHas('commissions', [
            'user_id' => $grand->id,
            'order_id' => $order->id,
            'level' => 2,
            'amount' => 25,
            'type' => 'repurchase',
        ]);
        $this->assertDatabaseHas('bv_commissions', [
            'user_id' => $parent->id,
            'order_id' => $order->id,
            'level' => 1,
            'amount' => 10,
        ]);
        $this->assertDatabaseHas('bv_commissions', [
            'user_id' => $grand->id,
            'order_id' => $order->id,
            'level' => 2,
            'amount' => 5,
        ]);

        $this->assertEquals(50, $parent->fresh()->wallet->commission_balance);
        $this->assertEquals(10, $parent->fresh()->wallet->earning_balance);

        // Buyer pays EMI 2 (500 INR)
        $payResp2 = $this->actingAs($buyer)->post('/credit/emis/' . $emi2->id . '/pay');
        $payResp2->assertSessionHas('success');

        // Total commissions after both EMIs:
        // Parent repurchase: 50 + 50 = 100 (exactly 10% of 1000)
        // Parent BV: 10 + 10 = 20 (exactly 10 BV * 2.0 rate)
        $this->assertEquals(100, $parent->fresh()->wallet->commission_balance);
        $this->assertEquals(20, $parent->fresh()->wallet->earning_balance);

        // Grandparent repurchase: 25 + 25 = 50 (exactly 5% of 1000)
        // Grandparent BV: 5 + 5 = 10 (exactly 10 BV * 1.0 rate)
        $this->assertEquals(50, $grand->fresh()->wallet->commission_balance);
        $this->assertEquals(10, $grand->fresh()->wallet->earning_balance);
    }

    public function test_split_purchase_distributes_upfront_cash_and_defers_credit_to_emi(): void
    {
        $grand = User::factory()->create(['status' => 'active', 'role' => 'user']);
        $parent = User::factory()->create(['status' => 'active', 'role' => 'user']);
        $buyer = User::factory()->create(['status' => 'active', 'role' => 'user']);

        Wallet::create(['user_id' => $grand->id, 'main_balance' => 0, 'commission_balance' => 0, 'earning_balance' => 0]);
        Wallet::create(['user_id' => $parent->id, 'main_balance' => 0, 'commission_balance' => 0, 'earning_balance' => 0]);
        Wallet::create(['user_id' => $buyer->id, 'main_balance' => 400, 'commission_balance' => 0, 'earning_balance' => 0]);

        DB::table('referrals')->insert([
            ['user_id' => $grand->id, 'parent_id' => null, 'referral_code' => 'G1', 'level_depth' => 0],
            ['user_id' => $parent->id, 'parent_id' => $grand->id, 'referral_code' => 'P1', 'level_depth' => 1],
            ['user_id' => $buyer->id, 'parent_id' => $parent->id, 'referral_code' => 'B1', 'level_depth' => 2],
        ]);

        $product = Product::create(['name' => 'Split Item', 'price' => 1000, 'stock' => 10, 'bv' => 10]);

        CreditAccount::create([
            'user_id' => $buyer->id,
            'credit_limit' => 2000,
            'used_credit' => 0,
            'available_credit' => 2000,
            'approval_status' => 'approved',
        ]);

        // Buyer has 400 in wallet, needs 1000 -> 400 Main Wallet + 600 Credit
        $response = $this->actingAs($buyer)->post('/shop/place-order', [
            'type' => 'product',
            'item_id' => $product->id,
            'quantity' => 1,
            'payment_method' => 'split',
        ]);

        $response->assertRedirect(route('dashboard'));

        $order = \App\Models\Order::where('user_id', $buyer->id)->first();
        $this->assertEquals(400, $order->paid_amount);
        $this->assertEquals(600, $order->credit_amount);

        // Buyer's order status is default 'processing' from shop controller,
        // let's complete the order to trigger upfront commission distribution
        $order->status = 'completed';
        $order->save();
        app(\App\Services\MLMService::class)->distributeOrderCommissions($order);

        // Upfront commission should ONLY be calculated on the 400 cash portion:
        // Parent repurchase: 10% of 400 = 40
        // Parent BV: 10 BV * (400 / 1000) = 4 BV -> 4 BV * 2.0 rate = 8 INR
        $this->assertEquals(40, $parent->fresh()->wallet->commission_balance);
        $this->assertEquals(8, $parent->fresh()->wallet->earning_balance);

        // EMIs for 600 credit: 500 and 100
        $emis = EmiSchedule::where('order_id', $order->id)->orderBy('id')->get();
        $this->assertCount(2, $emis);

        // Give buyer enough balance to pay EMI 1 (500)
        $buyer->wallet->update(['main_balance' => 1000]);

        // Buyer pays EMI 1 (500)
        $this->actingAs($buyer)->post('/credit/emis/' . $emis[0]->id . '/pay');

        // Parent gets additional:
        // Repurchase: 10% of 500 = 50 -> total 40 + 50 = 90
        // BV: 10 BV * (500 / 1000) = 5 BV -> 5 BV * 2.0 = 10 -> total 8 + 10 = 18
        $this->assertEquals(90, $parent->fresh()->wallet->commission_balance);
        $this->assertEquals(18, $parent->fresh()->wallet->earning_balance);

        // Buyer pays EMI 2 (100)
        $this->actingAs($buyer)->post('/credit/emis/' . $emis[1]->id . '/pay');

        // Parent gets additional:
        // Repurchase: 10% of 100 = 10 -> total 90 + 10 = 100 (exactly 10% of 1000!)
        // BV: 10 BV * (100 / 1000) = 1 BV -> 1 BV * 2.0 = 2 -> total 18 + 2 = 20 (exactly 10 BV * 2.0!)
        $this->assertEquals(100, $parent->fresh()->wallet->commission_balance);
        $this->assertEquals(20, $parent->fresh()->wallet->earning_balance);
    }

    public function test_auto_deducted_emi_via_command_distributes_commissions(): void
    {
        $parent = User::factory()->create(['status' => 'active', 'role' => 'user']);
        $buyer = User::factory()->create(['status' => 'active', 'role' => 'user']);

        Wallet::create(['user_id' => $parent->id, 'main_balance' => 0, 'commission_balance' => 0, 'earning_balance' => 0]);
        Wallet::create(['user_id' => $buyer->id, 'main_balance' => 500, 'commission_balance' => 0, 'earning_balance' => 0]);

        DB::table('referrals')->insert([
            ['user_id' => $parent->id, 'parent_id' => null, 'referral_code' => 'P1', 'level_depth' => 0],
            ['user_id' => $buyer->id, 'parent_id' => $parent->id, 'referral_code' => 'B1', 'level_depth' => 1],
        ]);

        $order = \App\Models\Order::create([
            'user_id' => $buyer->id,
            'total_amount' => 500,
            'total_bv' => 0,
            'paid_amount' => 0,
            'credit_amount' => 500,
            'payment_method' => 'credit_wallet',
            'status' => 'completed',
        ]);

        CreditAccount::create([
            'user_id' => $buyer->id,
            'credit_limit' => 1000,
            'used_credit' => 500,
            'available_credit' => 500,
            'approval_status' => 'approved',
        ]);

        $emi = EmiSchedule::create([
            'user_id' => $buyer->id,
            'order_id' => $order->id,
            'total_amount' => 500,
            'installment_amount' => 500,
            'interval_days' => 7,
            'due_date' => now()->subDay()->toDateString(), // Overdue today
            'status' => 'pending',
            'commission_distributed' => false,
        ]);

        // Run the console command to auto-pay
        $this->artisan('emi:apply-penalties')
            ->assertExitCode(0);

        // EMI should be paid and commission distributed
        $this->assertEquals('paid', $emi->fresh()->status);
        $this->assertTrue($emi->fresh()->commission_distributed);

        // Parent should have received 10% of 500 = 50 repurchase commission
        $this->assertEquals(50, $parent->fresh()->wallet->commission_balance);
    }
}
