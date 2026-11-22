<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\SettingController;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDashboardReportAndCreditPoolTest extends TestCase
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
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_view_dashboard_with_shop_initial_balance_report_card(): void
    {
        // Mock / set shop initial balance in settings
        $settings = SettingController::getSystemSettings();
        $settings['shop_initial_balance'] = 500000;
        Storage::disk('local')->put('settings.json', json_encode($settings));

        // Create an approved credit account with 15,000 limit and 5,000 used
        $ca = CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 15000,
            'used_credit' => 5000,
            'available_credit' => 10000,
            'approval_status' => 'approved',
        ]);

        // Place an order on credit
        Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 5000,
            'total_bv' => 0,
            'paid_amount' => 0,
            'credit_amount' => 5000,
            'payment_method' => 'credit_wallet',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Shop Initial Balance & Market Credit Capital Tracker');
        $response->assertSee('500,000.00'); // Initial balance
        $response->assertSee('5,000.00');   // Used credit in market
        $response->assertSee('495,000.00'); // Available credit balance (500000 - 5000)
        $response->assertSee('15,000.00');  // Total approved credit limit
    }

    public function test_dashboard_date_filtering_today_weekly_monthly_and_custom(): void
    {
        // Today order
        $todayOrder = Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 1200,
            'total_bv' => 0,
            'paid_amount' => 1200,
            'credit_amount' => 0,
            'payment_method' => 'main_wallet',
            'status' => 'completed',
        ]);
        $todayOrder->created_at = now();
        $todayOrder->save();

        // Past order (10 days ago)
        $pastOrder = Order::create([
            'user_id' => $this->customer->id,
            'total_amount' => 3000,
            'total_bv' => 0,
            'paid_amount' => 3000,
            'credit_amount' => 0,
            'payment_method' => 'main_wallet',
            'status' => 'completed',
        ]);
        $pastOrder->created_at = now()->subDays(10);
        $pastOrder->save();

        // 1. Today filter should only count the 1,200 order
        $todayResp = $this->actingAs($this->admin)->get(route('admin.dashboard', ['filter' => 'today']));
        $todayResp->assertStatus(200);
        $todayResp->assertSee('1,200.00');

        // 2. Weekly filter
        $weeklyResp = $this->actingAs($this->admin)->get(route('admin.dashboard', ['filter' => 'weekly']));
        $weeklyResp->assertStatus(200);

        // 3. Monthly filter
        $monthlyResp = $this->actingAs($this->admin)->get(route('admin.dashboard', ['filter' => 'monthly']));
        $monthlyResp->assertStatus(200);

        // 4. Custom range filter covering 15 days ago to now should count both (1200 + 3000 = 4,200)
        $customResp = $this->actingAs($this->admin)->get(route('admin.dashboard', [
            'filter' => 'custom',
            'start_date' => now()->subDays(15)->toDateString(),
            'end_date' => now()->toDateString(),
        ]));
        $customResp->assertStatus(200);
        $customResp->assertSee('4,200.00'); // 1200 + 3000
    }

    public function test_admin_can_export_summary_csv(): void
    {
        $settings = SettingController::getSystemSettings();
        $settings['shop_initial_balance'] = 750000;
        Storage::disk('local')->put('settings.json', json_encode($settings));

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard.export', ['filter' => 'all']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->getContent();

        $this->assertStringContainsString('Shop Initial Balance (Maximum Allowable Limit)', $content);
        $this->assertStringContainsString('750,000.00', $content);
        $this->assertStringContainsString('Credit in the Market (Total Used Credit)', $content);
        $this->assertStringContainsString('Available Credit Pool Balance', $content);
    }

    public function test_admin_can_update_shop_initial_balance_in_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'site_name' => 'Updated Store',
            'contact_email' => 'store@test.com',
            'shop_initial_balance' => 2000000,
            'enforce_shop_credit_pool_limit' => 'on',
            'currency' => 'INR',
            'default_credit_limit' => 10000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $saved = SettingController::getSystemSettings();
        $this->assertEquals(2000000, (float) $saved['shop_initial_balance']);
        $this->assertEquals('on', $saved['enforce_shop_credit_pool_limit']);
    }

    public function test_enforce_shop_credit_pool_limit_prevents_purchases_when_exhausted(): void
    {
        // Set small shop initial balance
        $settings = SettingController::getSystemSettings();
        $settings['shop_initial_balance'] = 5000;
        $settings['enforce_shop_credit_pool_limit'] = 'on';
        Storage::disk('local')->put('settings.json', json_encode($settings));

        // User has 10,000 limit, but 4,500 already used in market
        $ca = CreditAccount::create([
            'user_id' => $this->customer->id,
            'credit_limit' => 10000,
            'used_credit' => 4500,
            'available_credit' => 5500,
            'approval_status' => 'approved',
        ]);

        $product = Product::create([
            'name' => 'Premium Oil',
            'price' => 1000,
            'bv' => 10,
            'stock' => 10,
            'status' => 'active',
        ]);

        // Attempting to buy 1000 on credit (4500 + 1000 = 5500 > 5000 pool)
        $response = $this->actingAs($this->customer)->post(route('shop.place-order'), [
            'item_id' => $product->id,
            'type' => 'product',
            'quantity' => 1,
            'payment_method' => 'credit_wallet',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Shop maximum allowable credit limit pool reached', session('error'));
    }
}
