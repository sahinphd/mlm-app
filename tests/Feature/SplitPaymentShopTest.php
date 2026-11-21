<?php

namespace Tests\Feature;

use App\Models\CreditAccount;
use App\Models\EmiSchedule;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SplitPaymentShopTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        // Product costs 500, stock 10
        $this->product = Product::create([
            'name' => 'Rice 25kg Bag',
            'price' => 500,
            'bv' => 10,
            'stock' => 10,
            'status' => 'active',
        ]);
    }

    public function test_user_can_place_order_with_split_payment(): void
    {
        // Customer has 200 in wallet and 1000 in credit
        Wallet::create([
            'user_id' => $this->customer->id,
            'main_balance' => 200,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $ca = CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 1000,
            'used_credit' => 0,
            'available_credit' => 1000,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->customer)->post('/shop/place-order', [
            'item_id' => $this->product->id,
            'type' => 'product',
            'quantity' => 1,
            'payment_method' => 'split',
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        // Wallet balance drained to 0 (200 used)
        $this->assertEquals(0, $this->customer->fresh()->wallet->main_balance);

        // Credit account used 300 (500 - 200), available 700
        $caFresh = $ca->fresh();
        $this->assertEquals(300, $caFresh->used_credit);
        $this->assertEquals(700, $caFresh->available_credit);

        // Order created with payment_method 'split'
        $order = Order::where('user_id', $this->customer->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(500, $order->total_amount);
        $this->assertEquals('split', $order->payment_method);

        // EMI generated for only the 300 credit portion
        $emiSum = EmiSchedule::where('order_id', $order->id)->sum('installment_amount');
        $this->assertEquals(300, $emiSum);

        // Product stock decremented
        $this->assertEquals(9, $this->product->fresh()->stock);
    }

    public function test_admin_can_place_order_for_user_with_split_payment(): void
    {
        // Customer has 150 in wallet and 2000 in credit
        Wallet::create([
            'user_id' => $this->customer->id,
            'main_balance' => 150,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $ca = CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 2000,
            'used_credit' => 0,
            'available_credit' => 2000,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.shop.place-order'), [
            'user_id' => $this->customer->id,
            'item_id' => $this->product->id,
            'type' => 'product',
            'quantity' => 1,
            'payment_method' => 'split',
        ]);

        $response->assertRedirect(route('admin.orders'))
            ->assertSessionHas('success');

        // Wallet drained to 0 (150 used)
        $this->assertEquals(0, $this->customer->fresh()->wallet->main_balance);

        // Credit used 350, available 1650
        $caFresh = $ca->fresh();
        $this->assertEquals(350, $caFresh->used_credit);
        $this->assertEquals(1650, $caFresh->available_credit);

        // Order created with split
        $order = Order::where('user_id', $this->customer->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(500, $order->total_amount);
        $this->assertEquals('split', $order->payment_method);

        // EMI generated for 350
        $emiSum = EmiSchedule::where('order_id', $order->id)->sum('installment_amount');
        $this->assertEquals(350, $emiSum);
    }

    public function test_split_payment_fails_when_combined_balance_insufficient(): void
    {
        // Customer has 100 in wallet and 200 in credit (Total 300 < 500)
        Wallet::create([
            'user_id' => $this->customer->id,
            'main_balance' => 100,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 200,
            'used_credit' => 0,
            'available_credit' => 200,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->customer)->post('/shop/place-order', [
            'item_id' => $this->product->id,
            'type' => 'product',
            'quantity' => 1,
            'payment_method' => 'split',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(100, $this->customer->fresh()->wallet->main_balance);
        $this->assertEquals(10, $this->product->fresh()->stock);
    }
}
