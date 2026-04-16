<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\PaymentRequest;

class PaymentAndOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_payment_and_order_flow()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create();

        // Buyer creates a payment request
        $resp = $this->actingAs($buyer)->postJson('/api/payment-requests', [
            'amount' => 100,
            'method' => 'bank',
            'reference' => 'REF123'
        ]);
        $resp->assertStatus(201)->assertJsonStructure(['data' => ['id']]);

        $pr = PaymentRequest::first();

        // Admin approves the payment request
        $this->actingAs($admin)->postJson('/api/admin/payment-requests/'.$pr->id.'/approve', ['admin_note' => 'ok'])->assertStatus(200)->assertJson(['message' => 'Approved']);

        // Buyer wallet should be credited
        $this->actingAs($buyer)->getJson('/api/wallet')->assertStatus(200)->assertJsonFragment(['main_balance' => 100]);

        // Create a product and place an order using main wallet
        $product = Product::create(['name' => 'Test', 'price' => 50, 'stock' => 5, 'bv' => 2]);

        $this->actingAs($buyer)->postJson('/api/orders', [
            'items' => [ ['product_id' => $product->id, 'quantity' => 1] ],
            'payment_method' => 'main_wallet'
        ])->assertStatus(200)->assertJson(['message' => 'Order placed']);

        $this->assertDatabaseHas('orders', ['user_id' => $buyer->id, 'total_amount' => 50]);

        $wallet = Wallet::where('user_id', $buyer->id)->first();
        $this->assertNotNull($wallet);
        $this->assertEquals(50, $wallet->main_balance);
    }
}
