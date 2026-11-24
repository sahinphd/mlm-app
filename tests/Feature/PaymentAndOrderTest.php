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

    public function test_payments_view_renders_successfully()
    {
        $user = User::factory()->create(['kyc_status' => 'approved']);
        $response = $this->actingAs($user)->get('/payments');
        $response->assertStatus(200);
        $response->assertSee('UPI Wallet Top-up');
        $response->assertSee('Payable Amount');
        $response->assertSee('Open in UPI App');
    }

    public function test_manual_payment_and_order_flow()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['kyc_status' => 'approved']);

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

    public function test_admin_payments_view_renders_with_list_and_card_toggle()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

        PaymentRequest::create([
            'user_id' => $buyer->id,
            'amount' => 500,
            'method' => 'UPI',
            'reference' => 'UTR12345678',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/payments');
        $response->assertStatus(200);
        $response->assertSee('Wallet Top-up Requests');
        $response->assertSee('List');
        $response->assertSee('Card');
        $response->assertSee('UTR12345678');
        $response->assertSee('John Doe');
        $response->assertSee('Approve & Recharge', false);
    }

    public function test_admin_can_update_payment_comment_after_approval()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $paymentRequest = PaymentRequest::create([
            'user_id' => $buyer->id,
            'amount' => 1000,
            'method' => 'UPI',
            'reference' => 'UTR998877',
            'status' => 'approved',
            'admin_note' => 'Original note',
            'processed_at' => now(),
        ]);

        // Non-admin cannot update note
        $this->actingAs($buyer)->post('/admin/payments/' . $paymentRequest->id . '/note', [
            'admin_note' => 'Hacked note',
        ])->assertStatus(403);

        // Admin updates comment
        $response = $this->actingAs($admin)->post('/admin/payments/' . $paymentRequest->id . '/note', [
            'admin_note' => 'Updated comment after approval',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('payment_requests', [
            'id' => $paymentRequest->id,
            'admin_note' => 'Updated comment after approval',
        ]);

        // Verify page renders the updated comment and edit button
        $pageResponse = $this->actingAs($admin)->get('/admin/payments');
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Updated comment after approval');
        $pageResponse->assertSee('Edit Comment');
    }
}
