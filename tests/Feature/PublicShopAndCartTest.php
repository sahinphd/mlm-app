<?php

namespace Tests\Feature;

use App\Models\CreditAccount;
use App\Models\EmiSchedule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicShopAndCartTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product1;
    protected Product $product2;
    protected Package $package1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->product1 = Product::create([
            'name' => 'Basmati Rice 5kg',
            'price' => 450,
            'bv' => 25,
            'stock' => 20,
            'status' => 'active',
        ]);

        $this->product2 = Product::create([
            'name' => 'Pure Mustard Oil 1L',
            'price' => 180,
            'bv' => 10,
            'stock' => 15,
            'status' => 'active',
        ]);

        $this->package1 = Package::create([
            'name' => 'Family Grocery Combo Pack',
            'description' => 'Rice, Dal, Oil, and Spices bundled package',
            'price' => 1200,
            'bv' => 60,
            'status' => 'active',
        ]);
    }

    public function test_guest_can_access_shop_page(): void
    {
        $response = $this->get(route('shop.index'));

        $response->assertStatus(200);
        $response->assertSee('Basmati Rice 5kg');
        $response->assertSee('Pure Mustard Oil 1L');
        $response->assertSee('Family Grocery Combo Pack');
        $response->assertSee('Rs.450.00');
        $response->assertSee('Rs.180.00');
        $response->assertSee('Rs.1,200.00');
        $response->assertSee('Your Cart');
        $response->assertSee('Proceed to Checkout');
    }

    public function test_guest_can_sync_cart_to_session(): void
    {
        $cartData = [
            ['id' => $this->product1->id, 'type' => 'product', 'quantity' => 2],
            ['id' => $this->package1->id, 'type' => 'package', 'quantity' => 1],
        ];

        $response = $this->postJson(route('shop.cart.sync'), [
            'cart' => $cartData
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'count' => 2]);

        $this->assertEquals($cartData, session('shop_cart'));
    }

    public function test_guest_redirected_to_login_on_checkout_with_saved_cart(): void
    {
        $cartData = [
            ['id' => $this->product1->id, 'type' => 'product', 'quantity' => 1],
        ];

        $response = $this->post(route('shop.checkout'), [
            'cart' => $cartData
        ]);

        $response->assertRedirect(route('login'));
        $this->assertEquals(route('shop.checkout'), session('url.intended'));
        $this->assertEquals($cartData, session('shop_cart'));
    }

    public function test_authenticated_user_can_access_checkout_with_single_item(): void
    {
        Wallet::create([
            'user_id' => $this->user->id,
            'main_balance' => 1000,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $response = $this->actingAs($this->user)->get(route('shop.checkout', [
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]));

        $response->assertStatus(200);
        $response->assertSee('Basmati Rice 5kg');
        $response->assertSee('Rs.900.00');
        $response->assertSee('+50 BV');
        $response->assertSee('Main Wallet');
    }

    public function test_authenticated_user_can_access_checkout_with_multiple_cart_items(): void
    {
        Wallet::create([
            'user_id' => $this->user->id,
            'main_balance' => 2000,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $cart = [
            ['id' => $this->product1->id, 'type' => 'product', 'quantity' => 1],
            ['id' => $this->product2->id, 'type' => 'product', 'quantity' => 2],
            ['id' => $this->package1->id, 'type' => 'package', 'quantity' => 1],
        ];

        session(['shop_cart' => $cart]);

        $response = $this->actingAs($this->user)->get(route('shop.checkout'));

        $response->assertStatus(200);
        $response->assertSee('Basmati Rice 5kg');
        $response->assertSee('Pure Mustard Oil 1L');
        $response->assertSee('Family Grocery Combo Pack');
        // Total: 450 + (180 * 2 = 360) + 1200 = 2010
        $response->assertSee('Rs.2,010.00');
    }

    public function test_authenticated_user_can_place_multi_item_order_with_wallet(): void
    {
        Wallet::create([
            'user_id' => $this->user->id,
            'main_balance' => 2500,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $items = [
            ['id' => $this->product1->id, 'type' => 'product', 'quantity' => 2], // 450 * 2 = 900, bv: 50
            ['id' => $this->product2->id, 'type' => 'product', 'quantity' => 1], // 180, bv: 10
            ['id' => $this->package1->id, 'type' => 'package', 'quantity' => 1], // 1200, bv: 60
        ];
        // Total = 900 + 180 + 1200 = 2280, Total BV = 120

        session(['shop_cart' => $items]);

        $response = $this->actingAs($this->user)->post(route('shop.place-order'), [
            'payment_method' => 'main_wallet',
            'items' => $items,
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        // Wallet debited
        $this->assertEquals(220, (float)$this->user->fresh()->wallet->main_balance); // 2500 - 2280 = 220

        // Stock decremented
        $this->assertEquals(18, $this->product1->fresh()->stock); // 20 - 2 = 18
        $this->assertEquals(14, $this->product2->fresh()->stock); // 15 - 1 = 14

        // Order created
        $order = Order::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(2280, (float)$order->total_amount);
        $this->assertEquals(120, (float)$order->total_bv);
        $this->assertEquals('main_wallet', $order->payment_method);

        // Order items created
        $this->assertEquals(3, $order->items()->count());
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'price' => 450,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'package_id' => $this->package1->id,
            'quantity' => 1,
            'price' => 1200,
        ]);

        // Session cart is cleared
        $this->assertNull(session('shop_cart'));
    }

    public function test_authenticated_user_can_place_multi_item_order_with_split_payment(): void
    {
        Wallet::create([
            'user_id' => $this->user->id,
            'main_balance' => 500,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $creditAccount = CreditAccount::create([
            'user_id' => $this->user->id,
            'credit_limit' => 3000,
            'used_credit' => 0,
            'available_credit' => 3000,
            'approval_status' => 'approved',
        ]);

        // Total order = 450 + 180 = 630.
        // Wallet pays 500, Credit pays 130.
        $items = [
            ['id' => $this->product1->id, 'type' => 'product', 'quantity' => 1],
            ['id' => $this->product2->id, 'type' => 'product', 'quantity' => 1],
        ];

        $response = $this->actingAs($this->user)->post(route('shop.place-order'), [
            'payment_method' => 'split',
            'items' => $items,
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        // Wallet zeroed
        $this->assertEquals(0, (float)$this->user->fresh()->wallet->main_balance);

        // Credit deducted 130
        $caFresh = $creditAccount->fresh();
        $this->assertEquals(130, (float)$caFresh->used_credit);
        $this->assertEquals(2870, (float)$caFresh->available_credit);

        // EMI created for 130
        $order = Order::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertEquals('split', $order->payment_method);
        $this->assertEquals(630, (float)$order->total_amount);
        $this->assertEquals(500, (float)$order->paid_amount);
        $this->assertEquals(130, (float)$order->credit_amount);

        $emiTotal = EmiSchedule::where('order_id', $order->id)->sum('installment_amount');
        $this->assertEquals(130, (float)$emiTotal);
    }

    public function test_order_fails_when_product_stock_is_insufficient(): void
    {
        Wallet::create([
            'user_id' => $this->user->id,
            'main_balance' => 50000,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $response = $this->actingAs($this->user)->post(route('shop.place-order'), [
            'payment_method' => 'main_wallet',
            'items' => [
                ['id' => $this->product1->id, 'type' => 'product', 'quantity' => 50], // stock is only 20
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('out of stock', session('error'));
        $this->assertEquals(20, $this->product1->fresh()->stock);
    }

    public function test_home_page_and_layout_contain_shop_links(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee(route('shop.index'));
        $response->assertSee('Shop Catalog');
        $response->assertSee('Browse All Products');
    }

    public function test_authenticated_user_can_access_checkout_via_post_with_json_cart_string(): void
    {
        Wallet::create([
            'user_id' => $this->user->id,
            'main_balance' => 2000,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $cartJson = json_encode([
            ['id' => $this->product1->id, 'type' => 'product', 'quantity' => 2],
            ['id' => $this->package1->id, 'type' => 'package', 'quantity' => 1],
        ]);

        $response = $this->actingAs($this->user)->post(route('shop.checkout'), [
            'cart' => $cartJson,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Basmati Rice 5kg');
        $response->assertSee('Family Grocery Combo Pack');
        $this->assertIsArray(session('shop_cart'));
    }

    public function test_authenticated_user_can_access_checkout_when_session_cart_is_json_string(): void
    {
        Wallet::create([
            'user_id' => $this->user->id,
            'main_balance' => 2000,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $cartJson = json_encode([
            ['id' => $this->product1->id, 'type' => 'product', 'quantity' => 1],
        ]);

        session(['shop_cart' => $cartJson]);

        $response = $this->actingAs($this->user)->get(route('shop.checkout'));

        $response->assertStatus(200);
        $response->assertSee('Basmati Rice 5kg');
    }

    public function test_shows_topup_balance_request_option_and_call_help_icon_when_money_is_insufficient(): void
    {
        Wallet::create([
            'user_id' => $this->user->id,
            'main_balance' => 100, // order is 450, insufficient
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $response = $this->actingAs($this->user)->get(route('shop.checkout', [
            'product_id' => $this->product1->id,
            'quantity' => 1
        ]));

        $response->assertStatus(200);
        // Shows topup request button
        $response->assertSee('Request Balance Top-up');
        // Shows shortage
        $response->assertSee('Add Rs.350.00');
        // Shows Call Help icon and link
        $response->assertSee('Call Help');
        $response->assertSee('tel:+919091090858');
        // Checkout place order submit button is replaced
        $response->assertDontSee('Confirm & Place Order');
    }

    public function test_shows_checkout_button_when_money_is_sufficient(): void
    {
        Wallet::create([
            'user_id' => $this->user->id,
            'main_balance' => 1000, // order is 450, sufficient
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $response = $this->actingAs($this->user)->get(route('shop.checkout', [
            'product_id' => $this->product1->id,
            'quantity' => 1
        ]));

        $response->assertStatus(200);
        // Shows place order button
        $response->assertSee('Confirm & Place Order');
        // Also has help icon available
        $response->assertSee('tel:+919091090858');
    }
}
