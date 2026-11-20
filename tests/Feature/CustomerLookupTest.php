<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLookupTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'John Customer',
            'email' => 'john@test.com',
            'phone' => '9876543210',
            'role' => 'user',
            'status' => 'active',
        ]);

        $wallet = Wallet::create([
            'user_id' => $this->customer->id,
            'main_balance' => 500,
            'earning_balance' => 100,
            'credit_balance' => 0,
        ]);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'source' => 'manual',
            'amount' => 500,
            'reference_id' => 'INIT-500',
            'description' => 'Initial test balance',
        ]);
    }

    public function test_non_admin_cannot_access_customer_lookup(): void
    {
        $response = $this->actingAs($this->customer)->get('/admin/customer-lookup');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_customer_lookup_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/customer-lookup');
        $response->assertStatus(200);
        $response->assertSee('Customer Lookup');
    }

    public function test_sidebar_contains_customer_lookup_link(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Customer Lookup');
        $response->assertSee(route('admin.customer-lookup.index'));
    }

    public function test_search_by_id_returns_customer_and_transactions(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/search?q=' . $this->customer->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'customer' => [
                    'id' => $this->customer->id,
                    'name' => 'John Customer',
                    'email' => 'john@test.com',
                ],
            ]);

        $this->assertCount(1, $response->json('transactions'));
    }

    public function test_search_by_email_returns_customer(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/search?q=john@test.com');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'customer' => [
                    'id' => $this->customer->id,
                    'email' => 'john@test.com',
                ],
            ]);
    }

    public function test_search_by_phone_returns_customer(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/search?q=9876543210');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'customer' => [
                    'id' => $this->customer->id,
                    'phone' => '9876543210',
                ],
            ]);
    }

    public function test_key_up_live_suggestions(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/search?q=john&suggest=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'matches' => [
                    '*' => ['id', 'name', 'email', 'phone', 'status', 'avatar_url', 'main_balance_formatted']
                ]
            ]);

        $this->assertEquals('John Customer', $response->json('matches.0.name'));
    }

    public function test_select_customer_by_user_id(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/search?user_id=' . $this->customer->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'customer' => [
                    'id' => $this->customer->id,
                    'name' => 'John Customer',
                ],
            ]);
    }

    public function test_search_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/search?q=nonexistent@test.com');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_admin_can_add_wallet_money(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/admin/customer-lookup/' . $this->customer->id . '/add-money', [
            'amount' => 250,
            'note' => 'Test admin deposit',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(750, $this->customer->fresh()->wallet->main_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $this->customer->wallet->id,
            'type' => 'credit',
            'amount' => 250,
            'source' => 'manual',
            'description' => 'Test admin deposit',
        ]);
    }

    public function test_admin_can_deduct_shop_amount(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/admin/customer-lookup/' . $this->customer->id . '/shop-deduct', [
            'amount' => 200,
            'note' => 'In-store grocery purchase',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(300, $this->customer->fresh()->wallet->main_balance);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'total_amount' => 200,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $this->customer->wallet->id,
            'type' => 'debit',
            'amount' => 200,
            'source' => 'purchase',
            'description' => 'In-store grocery purchase',
        ]);
    }

    public function test_shop_deduct_distributes_commissions_to_upline_sponsors(): void
    {
        // Create an active upline sponsor
        $upline = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);
        $uplineWallet = Wallet::create([
            'user_id' => $upline->id,
            'main_balance' => 0,
            'commission_balance' => 0,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        // Establish referral hierarchy: customer -> upline
        \Illuminate\Support\Facades\DB::table('referrals')->updateOrInsert(
            ['user_id' => $this->customer->id],
            [
                'parent_id' => $upline->id,
                'referral_code' => 'TESTUPLINE',
                'level_depth' => 1,
            ]
        );

        $response = $this->actingAs($this->admin)->postJson('/admin/customer-lookup/' . $this->customer->id . '/shop-deduct', [
            'amount' => 200,
            'note' => 'Purchase with upline commission',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify order created
        $order = \App\Models\Order::where('user_id', $this->customer->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(200, $order->total_amount);

        // Verify repurchase commission record created for upline
        $commission = \App\Models\Commission::where('order_id', $order->id)
            ->where('user_id', $upline->id)
            ->where('from_user_id', $this->customer->id)
            ->where('type', 'repurchase')
            ->first();

        $this->assertNotNull($commission);
        $this->assertGreaterThan(0, $commission->amount);

        // Verify upline wallet commission balance was credited
        $uplineWalletFresh = $uplineWallet->fresh();
        $this->assertEquals($commission->amount, $uplineWalletFresh->commission_balance);
    }

    public function test_admin_cannot_deduct_more_than_available_balance(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/admin/customer-lookup/' . $this->customer->id . '/shop-deduct', [
            'amount' => 1000, // available is 500
            'note' => 'Excessive purchase',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertEquals(500, $this->customer->fresh()->wallet->main_balance);
    }

    public function test_transactions_paginate_10_by_default_and_support_filters(): void
    {
        // Create 15 transactions
        for ($i = 1; $i <= 15; $i++) {
            \App\Models\WalletTransaction::create([
                'wallet_id' => $this->customer->wallet->id,
                'type' => ($i % 2 === 0) ? 'credit' : 'debit',
                'amount' => 10 * $i,
                'source' => 'manual',
                'description' => 'Tx item #' . $i,
            ]);
        }

        // Test default 10 per page
        $response = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/' . $this->customer->id . '/transactions');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 10,
                ],
            ]);
        $this->assertCount(10, $response->json('transactions'));

        // Test filter by type=credit
        $creditResp = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/' . $this->customer->id . '/transactions?type=credit');
        $creditResp->assertStatus(200);
        foreach ($creditResp->json('transactions') as $tx) {
            $this->assertEquals('Credit', $tx['type']);
        }

        // Test filter by search keyword
        $searchResp = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/' . $this->customer->id . '/transactions?search_tx=' . urlencode('Tx item #5'));
        $searchResp->assertStatus(200);
        $this->assertCount(1, $searchResp->json('transactions'));
    }
}
