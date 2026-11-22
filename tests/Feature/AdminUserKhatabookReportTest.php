<?php

namespace Tests\Feature;

use App\Models\CreditAccount;
use App\Models\EmiSchedule;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserKhatabookReportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $creditorUser;
    protected $nonCreditorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Boss',
            'email' => 'admin@khatatest.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->creditorUser = User::factory()->create([
            'name' => 'Ramesh Creditor',
            'email' => 'ramesh@khatatest.com',
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->nonCreditorUser = User::factory()->create([
            'name' => 'Suresh CashOnly',
            'email' => 'suresh@khatatest.com',
            'role' => 'user',
            'status' => 'active',
        ]);

        // Ramesh has an approved credit account with 20,000 limit and 7,500 used (Udhar)
        CreditAccount::create([
            'user_id' => $this->creditorUser->id,
            'credit_limit' => 20000,
            'used_credit' => 7500,
            'available_credit' => 12500,
            'approval_status' => 'approved',
        ]);

        // Suresh has NO credit account (or 0 limit)
    }

    public function test_admin_can_view_users_page_with_khatabook_summary_cards(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users'));

        $response->assertStatus(200);
        $response->assertSee('User Directory &amp; Khatabook Report', false);
        $response->assertSee('Total Udhar (Due)');
        $response->assertSee('7,500.00'); // Ramesh's used credit
        $response->assertSee('Creditors');
        $response->assertSee('Non-Creditors');
        $response->assertSee('All Users');
        $response->assertSee('Showing All Customers');
    }

    public function test_users_data_endpoint_returns_all_users_by_default(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.data'));

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertGreaterThanOrEqual(3, $json['recordsTotal']); // admin, ramesh, suresh
        $allNames = collect($json['data'])->pluck(2)->implode(' ');
        $this->assertStringContainsString('Ramesh Creditor', $allNames);
        $this->assertStringContainsString('Suresh CashOnly', $allNames);
    }

    public function test_users_data_filters_creditors_only(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.data', ['credit_type' => 'creditor']));

        $response->assertStatus(200);
        $json = $response->json();

        $allNames = collect($json['data'])->pluck(2)->implode(' ');
        $this->assertStringContainsString('Ramesh Creditor', $allNames);
        $this->assertStringNotContainsString('Suresh CashOnly', $allNames);
        $this->assertStringNotContainsString('Admin Boss', $allNames);

        // Check Khata badge and amount formatting
        $firstRow = $json['data'][0];
        $this->assertStringContainsString('Creditor', $firstRow[4]); // Khata Type badge
        $this->assertStringContainsString('20,000.00', $firstRow[7]); // Limit
        $this->assertStringContainsString('7,500.00', $firstRow[8]);  // Khata Due
        $this->assertStringContainsString('12,500.00', $firstRow[9]); // Available
        $this->assertStringContainsString('📖 Khata', $firstRow[12]);  // Khata action button
    }

    public function test_users_data_filters_non_creditors_only(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.data', ['credit_type' => 'non_creditor']));

        $response->assertStatus(200);
        $json = $response->json();

        $allNames = collect($json['data'])->pluck(2)->implode(' ');
        $this->assertStringContainsString('Suresh CashOnly', $allNames);
        $this->assertStringNotContainsString('Ramesh Creditor', $allNames);

        // Check Non-Creditor badge
        $rows = collect($json['data']);
        $sureshRow = $rows->first(function($row) {
            return str_contains($row[2], 'Suresh CashOnly');
        });
        $this->assertNotNull($sureshRow);
        $this->assertStringContainsString('Non-Creditor', $sureshRow[4]);
    }

    public function test_users_data_returns_compact_khatabook_columns_and_collapsible_drawer(): void
    {
        // Give Ramesh some shopping orders
        Order::create([
            'user_id' => $this->creditorUser->id,
            'total_amount' => 3450.00,
            'total_bv' => 150,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.data', ['credit_type' => 'creditor']));
        $response->assertStatus(200);
        $json = $response->json();

        $firstRow = $json['data'][0];

        // 1: Avatar initials
        $this->assertStringContainsString('RC', $firstRow[1]); // Ramesh Creditor initials

        // 2: Name & Phone
        $this->assertStringContainsString('Ramesh Creditor', $firstRow[2]);

        // 6: Total Shopping Done
        $this->assertStringContainsString('3,450.00', $firstRow[6]);

        // 8: Khata Due (Udhar) with You'll Get
        $this->assertStringContainsString("You'll Get", $firstRow[8]);
        $this->assertStringContainsString('7,500.00', $firstRow[8]);

        // 12: Action buttons with Khatabook statement and Details toggle
        $this->assertStringContainsString('📖 Khata', $firstRow[12]);
        $this->assertStringContainsString('Details', $firstRow[12]);

        // 13: Rich Collapsible Child Drawer HTML
        $this->assertNotNull($firstRow[13]);
        $this->assertStringContainsString('KYC &amp; Verification', $firstRow[13]);
        $this->assertStringContainsString('Contact &amp; Nominee', $firstRow[13]);
        $this->assertStringContainsString('Network &amp; Earnings', $firstRow[13]);
        $this->assertStringContainsString('Open Customer Statement', $firstRow[13]);
        $this->assertStringContainsString('View Profile', $firstRow[13]);
        $this->assertStringContainsString('Genealogy Tree', $firstRow[13]);

        // 14: Mobile Compact Card HTML
        $this->assertNotNull($firstRow[14]);
        $this->assertStringContainsString('user-mobile-card', $firstRow[14]);
        $this->assertStringContainsString('Ramesh Creditor', $firstRow[14]);
        $this->assertStringContainsString('Khata Due (Udhar)', $firstRow[14]);
        $this->assertStringContainsString("You'll Get", $firstRow[14]);
        $this->assertStringContainsString('7,500.00', $firstRow[14]);
        $this->assertStringContainsString('Credit Balance', $firstRow[14]);
        $this->assertStringContainsString('Main Wallet', $firstRow[14]);
        $this->assertStringContainsString('Shop Done', $firstRow[14]);
        $this->assertStringContainsString('📖 Open Khata', $firstRow[14]);
        $this->assertStringContainsString('card-child-drawer', $firstRow[14]);
    }

    public function test_admin_users_view_renders_cards_and_table_view_modes_and_uniform_cards(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users'));

        $response->assertStatus(200);
        $response->assertSee('btnViewCards', false);
        $response->assertSee('btnViewTable', false);
        $response->assertSee('Cards View');
        $response->assertSee('Table View');
        $response->assertSee('mobileCardsContainer', false);
        $response->assertSee('customUserSearchInput', false);
        $response->assertSee('Single-Screen Khata');
        $response->assertSee('Create New User');
    }
}
