<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Models\WalletTransaction;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeAndLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $sender;
    protected User $receiver;
    protected Wallet $senderWallet;
    protected Wallet $receiverWallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->sender = User::factory()->create([
            'name' => 'Alice Sender',
            'email' => 'alice@test.com',
            'phone' => '9876543211',
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->receiver = User::factory()->create([
            'name' => 'Bob Receiver',
            'email' => 'bob@test.com',
            'phone' => '9876543222',
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->senderWallet = Wallet::create([
            'user_id' => $this->sender->id,
            'main_balance' => 1000.00,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $this->receiverWallet = Wallet::create([
            'user_id' => $this->receiver->id,
            'main_balance' => 200.00,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);
    }

    public function test_ledger_service_parses_qr_payloads_correctly(): void
    {
        $service = app(LedgerService::class);

        // 1. CUST:<id> format
        $this->assertEquals($this->receiver->id, $service->parseQrPayload("CUST:{$this->receiver->id}"));

        // 2. CUST-<id> (with 10000 offset or direct)
        $offsetId = 10000 + $this->receiver->id;
        $this->assertEquals($this->receiver->id, $service->parseQrPayload("CUST-{$offsetId}"));

        // 3. Plain numeric ID
        $this->assertEquals($this->receiver->id, $service->parseQrPayload((string) $this->receiver->id));

        // 4. By phone
        $this->assertEquals($this->receiver->id, $service->parseQrPayload('9876543222'));

        // 5. By email
        $this->assertEquals($this->receiver->id, $service->parseQrPayload('bob@test.com'));

        // 6. Non-existent returns null
        $this->assertNull($service->parseQrPayload('CUST:999999'));
    }

    public function test_customer_can_view_my_qr_code_page(): void
    {
        $response = $this->actingAs($this->sender)->get(route('wallet.my-qr'));

        $response->assertStatus(200)
            ->assertSee('My Customer QR Code')
            ->assertSee('Alice Sender')
            ->assertSee('CUST:' . $this->sender->id)
            ->assertSee('CUST-' . (10000 + $this->sender->id))
            ->assertSee('Save PNG')
            ->assertSee('Print');
    }

    public function test_customer_can_view_scan_pay_page(): void
    {
        $response = $this->actingAs($this->sender)->get(route('wallet.scan-pay'));

        $response->assertStatus(200)
            ->assertSee('Scan QR & Pay', false)
            ->assertSee('Live Camera Scanner')
            ->assertSee('Manual Fallback: Search Customer ID')
            ->assertSee('Available Balance');
    }

    public function test_resolve_recipient_endpoint_success(): void
    {
        $response = $this->actingAs($this->sender)->postJson(route('wallet.resolve-recipient'), [
            'code' => 'CUST:' . $this->receiver->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'recipient' => [
                    'id' => $this->receiver->id,
                    'name' => 'Bob Receiver',
                    'cust_id' => 'CUST-' . (10000 + $this->receiver->id),
                ],
            ]);
    }

    public function test_resolve_recipient_prevents_self_transfer(): void
    {
        $response = $this->actingAs($this->sender)->postJson(route('wallet.resolve-recipient'), [
            'code' => 'CUST:' . $this->sender->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Self-transfer disallowed. You scanned your own QR code.',
            ]);
    }

    public function test_qr_transfer_executes_atomically_and_updates_balances_and_ledgers(): void
    {
        $amount = 350.00;

        $response = $this->actingAs($this->sender)->postJson(route('wallet.qr-transfer'), [
            'recipient_id' => $this->receiver->id,
            'amount' => $amount,
            'remarks' => 'Lunch payment test',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'receipt' => [
                    'amount' => 350.00,
                    'sender_new_balance' => 650.00,
                    'recipient_new_balance' => 550.00,
                ],
            ]);

        // 1. Verify Wallet Balances updated
        $this->assertEquals(650.00, $this->senderWallet->fresh()->main_balance);
        $this->assertEquals(550.00, $this->receiverWallet->fresh()->main_balance);

        // 2. Verify WalletTransactions
        $senderTx = WalletTransaction::where('wallet_id', $this->senderWallet->id)->latest('id')->first();
        $receiverTx = WalletTransaction::where('wallet_id', $this->receiverWallet->id)->latest('id')->first();

        $this->assertNotNull($senderTx);
        $this->assertEquals('debit', $senderTx->type);
        $this->assertEquals('transfer', $senderTx->source);
        $this->assertEquals(350.00, (float) $senderTx->amount);
        $this->assertEquals(650.00, (float) $senderTx->balance);
        $this->assertEquals($this->receiver->id, $senderTx->counterparty_id);
        $this->assertEquals('completed', $senderTx->status);

        $this->assertNotNull($receiverTx);
        $this->assertEquals('credit', $receiverTx->type);
        $this->assertEquals('transfer', $receiverTx->source);
        $this->assertEquals(350.00, (float) $receiverTx->amount);
        $this->assertEquals(550.00, (float) $receiverTx->balance);
        $this->assertEquals($this->sender->id, $receiverTx->counterparty_id);
        $this->assertEquals('completed', $receiverTx->status);

        // 3. Verify Immutable WalletLedgers
        $senderLedger = WalletLedger::where('user_id', $this->sender->id)->latest('id')->first();
        $receiverLedger = WalletLedger::where('user_id', $this->receiver->id)->latest('id')->first();

        $this->assertNotNull($senderLedger);
        $this->assertEquals('debit', $senderLedger->type);
        $this->assertEquals('qr_transfer', $senderLedger->source);
        $this->assertEquals(350.00, (float) $senderLedger->amount);
        $this->assertEquals(650.00, (float) $senderLedger->resulting_balance);
        $this->assertEquals('Bob Receiver', $senderLedger->counterparty_name);

        $this->assertNotNull($receiverLedger);
        $this->assertEquals('credit', $receiverLedger->type);
        $this->assertEquals('qr_transfer', $receiverLedger->source);
        $this->assertEquals(350.00, (float) $receiverLedger->amount);
        $this->assertEquals(550.00, (float) $receiverLedger->resulting_balance);
        $this->assertEquals('Alice Sender', $receiverLedger->counterparty_name);
    }

    public function test_qr_transfer_fails_when_insufficient_balance(): void
    {
        $response = $this->actingAs($this->sender)->postJson(route('wallet.qr-transfer'), [
            'recipient_id' => $this->receiver->id,
            'amount' => 5000.00,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        // Balances remain untouched
        $this->assertEquals(1000.00, $this->senderWallet->fresh()->main_balance);
        $this->assertEquals(200.00, $this->receiverWallet->fresh()->main_balance);
    }

    public function test_admin_search_by_qr_payload_preloads_customer_lookup(): void
    {
        // Admin searches with scanned QR payload "CUST:<id>"
        $response = $this->actingAs($this->admin)->getJson('/admin/customer-lookup/search?q=CUST:' . $this->receiver->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'customer' => [
                    'id' => $this->receiver->id,
                    'name' => 'Bob Receiver',
                    'cust_id' => 'CUST-' . (10000 + $this->receiver->id),
                ],
            ]);
    }

    public function test_admin_customer_lookup_add_money_writes_atomic_ledger(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.customer-lookup.add-money', ['user' => $this->receiver->id]), [
            'amount' => 250,
            'note' => 'Admin QR topup',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Receiver wallet was 200, now 450
        $this->assertEquals(450.00, $this->receiverWallet->fresh()->main_balance);

        $ledger = WalletLedger::where('user_id', $this->receiver->id)->latest('id')->first();
        $this->assertNotNull($ledger);
        $this->assertEquals('credit', $ledger->type);
        $this->assertEquals('manual', $ledger->source);
        $this->assertEquals(250.00, (float) $ledger->amount);
        $this->assertEquals(450.00, (float) $ledger->resulting_balance);
    }

    public function test_admin_customer_lookup_shop_deduct_writes_atomic_ledger(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.customer-lookup.shop-deduct', ['user' => $this->receiver->id]), [
            'amount' => 100,
            'bv' => 10,
            'note' => 'Admin QR grocery billing',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Receiver wallet was 200, now 100
        $this->assertEquals(100.00, $this->receiverWallet->fresh()->main_balance);

        $ledger = WalletLedger::where('user_id', $this->receiver->id)->latest('id')->first();
        $this->assertNotNull($ledger);
        $this->assertEquals('debit', $ledger->type);
        $this->assertEquals('purchase', $ledger->source);
        $this->assertEquals(100.00, (float) $ledger->amount);
        $this->assertEquals(100.00, (float) $ledger->resulting_balance);
    }
}
