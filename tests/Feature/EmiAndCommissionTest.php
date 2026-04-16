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
use Illuminate\Support\Facades\DB;

class EmiAndCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected function seedSettings()
    {
        DB::table('settings')->insertOrIgnore([
            ['key' => 'bv_value', 'value' => '1'],
            ['key' => 'payout_cap_percent', 'value' => '60'],
            ['key' => 'emi_installments', 'value' => '4'],
            ['key' => 'emi_interval_days', 'value' => '7'],
        ]);
    }

    public function test_credit_purchase_generates_emi_and_commissions()
    {
        $this->seedSettings();

        // Build referral chain: grandparent -> parent -> buyer
        $grand = User::factory()->create();
        $parent = User::factory()->create();
        $buyer = User::factory()->create();

        // Insert referrals (direct DB writes matching migration)
        DB::table('referrals')->insert([
            ['user_id' => $grand->id, 'parent_id' => null, 'referral_code' => 'G1', 'level_depth' => 0],
            ['user_id' => $parent->id, 'parent_id' => $grand->id, 'referral_code' => 'P1', 'level_depth' => 1],
            ['user_id' => $buyer->id, 'parent_id' => $parent->id, 'referral_code' => 'B1', 'level_depth' => 2],
        ]);

        // Create product with BV
        $product = Product::create(['name' => 'CreditItem', 'price' => 200, 'stock' => 10, 'bv' => 5]);

        // Approve credit account for buyer
        CreditAccount::create(['user_id' => $buyer->id, 'credit_limit' => 1000, 'used_credit' => 0, 'available_credit' => 1000, 'approval_status' => 'approved']);

        // Buyer purchases using credit
        $resp = $this->actingAs($buyer)->postJson('/api/orders', [
            'items' => [ ['product_id' => $product->id, 'quantity' => 1] ],
            'payment_method' => 'credit_wallet'
        ]);

        $resp->assertStatus(200)->assertJson(['message' => 'Order placed']);

        // EMI schedules should be created
        $this->assertDatabaseHas('emi_schedules', ['user_id' => $buyer->id, 'order_id' => 1, 'status' => 'pending']);

        // Commissions: parent and grand should receive (levels 1 and 2) - check earning_balance
        $this->assertDatabaseHas('wallets', ['user_id' => $parent->id]);
        $this->assertDatabaseHas('wallets', ['user_id' => $grand->id]);

        $parentWallet = Wallet::where('user_id', $parent->id)->first();
        $grandWallet = Wallet::where('user_id', $grand->id)->first();

        $this->assertNotNull($parentWallet);
        $this->assertNotNull($grandWallet);

        $this->assertGreaterThan(0, $parentWallet->earning_balance);
        $this->assertGreaterThan(0, $grandWallet->earning_balance);
    }

    public function test_commission_scaling_applies_when_cap_exceeded()
    {
        $this->seedSettings();

        // Create deeper chain and a very high BV order so gross > cap
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $u3 = User::factory()->create();
        DB::table('referrals')->insert([
            ['user_id'=>$u1->id,'parent_id'=>null,'referral_code'=>'A','level_depth'=>0],
            ['user_id'=>$u2->id,'parent_id'=>$u1->id,'referral_code'=>'B','level_depth'=>1],
            ['user_id'=>$u3->id,'parent_id'=>$u2->id,'referral_code'=>'C','level_depth'=>2],
        ]);

        $product = Product::create(['name'=>'BigBV','price'=>1000,'stock'=>5,'bv'=>100]);
        CreditAccount::create(['user_id'=>$u3->id,'credit_limit'=>10000,'used_credit'=>0,'available_credit'=>10000,'approval_status'=>'approved']);

        $this->actingAs($u3)->postJson('/api/orders', ['items'=>[['product_id'=>$product->id,'quantity'=>1]],'payment_method'=>'credit_wallet'])->assertStatus(200);

        // Sum of commissions recorded should not exceed payout cap percent of order total
        $order = DB::table('orders')->first();
        $commSum = DB::table('commissions')->where('order_id',$order->id)->sum('amount');
        $maxPayout = $order->total_amount * (60/100);

        $this->assertLessThanOrEqual($maxPayout + 0.01, $commSum);
    }

    public function test_insufficient_credit_blocks_order()
    {
        $this->seedSettings();
        $user = User::factory()->create();
        DB::table('referrals')->insert(['user_id'=>$user->id,'parent_id'=>null,'referral_code'=>'X','level_depth'=>0]);
        $product = Product::create(['name'=>'Expensive','price'=>5000,'stock'=>1,'bv'=>50]);
        CreditAccount::create(['user_id'=>$user->id,'credit_limit'=>1000,'used_credit'=>0,'available_credit'=>1000,'approval_status'=>'approved']);

        $this->actingAs($user)->postJson('/api/orders', ['items'=>[['product_id'=>$product->id,'quantity'=>1]],'payment_method'=>'credit_wallet'])->assertStatus(400)->assertJsonFragment(['message' => 'Insufficient credit']);
    }
}
