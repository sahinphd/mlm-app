<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBulkIdCardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer1;
    protected User $customer2;
    protected User $customer3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Boss',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->customer1 = User::factory()->create([
            'name' => 'Subhamita Das',
            'email' => 'subhamita@test.com',
            'phone' => '9876543210',
            'role' => 'user',
            'status' => 'active',
            'kyc_status' => 'approved',
            'aadhaar_number' => '1234-5678-9012',
            'address' => 'Kolkata, West Bengal',
        ]);

        Referral::create([
            'user_id' => $this->customer1->id,
            'parent_id' => null,
            'referral_code' => 'SUBH101',
            'position' => 'left',
        ]);

        Wallet::create([
            'user_id' => $this->customer1->id,
            'main_balance' => 500,
            'earning_balance' => 150,
            'credit_balance' => 2000,
        ]);

        $this->customer2 = User::factory()->create([
            'name' => 'Rohan Mukherjee',
            'email' => 'rohan@test.com',
            'phone' => '9876543211',
            'role' => 'user',
            'status' => 'active',
            'kyc_status' => 'pending',
        ]);

        Referral::create([
            'user_id' => $this->customer2->id,
            'parent_id' => null,
            'referral_code' => 'ROHAN202',
            'position' => 'right',
        ]);

        $this->customer3 = User::factory()->create([
            'name' => 'Ananya Sen',
            'email' => 'ananya@test.com',
            'phone' => '9876543212',
            'role' => 'admin',
            'status' => 'active',
            'kyc_status' => 'unfilled',
        ]);
    }

    public function test_guest_and_non_admin_cannot_access_id_card_routes(): void
    {
        // Guest
        $this->get(route('admin.id-cards.index'))->assertRedirect(route('login'));
        $this->get(route('admin.id-cards.print', ['user_ids' => [$this->customer1->id]]))->assertRedirect(route('login'));

        // Non-admin customer
        $this->actingAs($this->customer1)->get(route('admin.id-cards.index'))->assertStatus(403);
        $this->actingAs($this->customer1)->get(route('admin.id-cards.print', ['user_ids' => [$this->customer1->id]]))->assertStatus(403);
    }

    public function test_admin_can_access_id_card_index_and_see_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.id-cards.index'));
        $response->assertStatus(200);

        $response->assertSee('Bulk ID Cards &amp; QR Passes', false);
        $response->assertSee('Subhamita Das');
        $response->assertSee('Rohan Mukherjee');
        $response->assertSee('Ananya Sen');
        $response->assertSee('SUBH101');
        $response->assertSee('Print Selected (0)');
    }

    public function test_admin_can_search_and_filter_users_for_id_cards(): void
    {
        // Search by name
        $response = $this->actingAs($this->admin)->get(route('admin.id-cards.index', ['search' => 'Subhamita']));
        $response->assertStatus(200);
        $response->assertSee('Subhamita Das');
        $response->assertDontSee('Rohan Mukherjee');

        // Filter by KYC status
        $responseKyc = $this->actingAs($this->admin)->get(route('admin.id-cards.index', ['kyc_status' => 'approved']));
        $responseKyc->assertStatus(200);
        $responseKyc->assertSee('Subhamita Das');
        $responseKyc->assertDontSee('Rohan Mukherjee');

        // Filter by role
        $responseRole = $this->actingAs($this->admin)->get(route('admin.id-cards.index', ['role' => 'admin']));
        $responseRole->assertStatus(200);
        $responseRole->assertSee('Ananya Sen');
        $responseRole->assertDontSee('Subhamita Das');
    }

    public function test_admin_can_generate_bulk_id_card_print_preview_both_sides(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.id-cards.print'), [
            'user_ids' => [$this->customer1->id, $this->customer2->id],
            'layout' => 'a4',
            'side' => 'both',
            'cutting_guides' => 1,
        ]);

        $response->assertStatus(200);

        // Verification of Front Side elements
        $response->assertSee('card-front-wrapper');
        $response->assertSee('SUBHAMITA DAS');
        $response->assertSee('ROHAN MUKHERJEE');
        $response->assertSee('barcode-' . $this->customer1->id);
        $response->assertSee('front-qr-' . $this->customer1->id);
        $response->assertSee('REF:');
        $response->assertSee('SUBH101');

        // Verification of Back Side elements
        $response->assertSee('card-back-wrapper');
        $response->assertSee('back-qr-' . $this->customer1->id);
        $response->assertSee('SCAN &amp; PAY', false);
        $response->assertSee('Instant Settlement');
        $response->assertSee('CUST-' . (10000 + $this->customer1->id));
    }

    public function test_admin_can_print_front_side_only(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.id-cards.print', [
            'user_ids' => [$this->customer1->id],
            'side' => 'front',
            'layout' => 'a4',
        ]));

        $response->assertStatus(200);
        $response->assertSee('card-front-wrapper');
        $response->assertDontSee('card-back-wrapper');
        $response->assertSee('barcode-' . $this->customer1->id);
    }

    public function test_admin_can_print_back_side_only(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.id-cards.print', [
            'user_ids' => [$this->customer1->id],
            'side' => 'back',
            'layout' => 'cr80',
        ]));

        $response->assertStatus(200);
        $response->assertSee('card-back-wrapper');
        $response->assertDontSee('card-front-wrapper');
        $response->assertSee('back-qr-' . $this->customer1->id);
        $response->assertSee('print-mode-cr80');
    }

    public function test_admin_can_select_all_matching_filter(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.id-cards.print'), [
            'select_all' => 1,
            'role' => 'user',
        ]);

        $response->assertStatus(200);
        $response->assertSee('SUBHAMITA DAS');
        $response->assertSee('ROHAN MUKHERJEE');
        $response->assertDontSee('ANANYA SEN');
    }

    public function test_single_print_shortcut_redirects_to_print_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.id-cards.single-print', $this->customer1->id));
        $response->assertRedirect();
        $this->assertStringContainsString('admin/id-cards/print', $response->headers->get('Location'));
        $this->assertStringContainsString((string)$this->customer1->id, $response->headers->get('Location'));
    }

    public function test_admin_users_and_sidebar_contain_id_card_links(): void
    {
        $responseUsers = $this->actingAs($this->admin)->get(route('admin.users'));
        $responseUsers->assertStatus(200);
        $responseUsers->assertSee(route('admin.id-cards.index'));
        $responseUsers->assertSee('Bulk ID Cards');

        $responseShow = $this->actingAs($this->admin)->get(route('admin.users.show', $this->customer1->id));
        $responseShow->assertStatus(200);
        $responseShow->assertSee(route('admin.id-cards.single-print', $this->customer1->id));
        $responseShow->assertSee('Print ID Card');
    }
}
