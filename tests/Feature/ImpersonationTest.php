<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $secondAdmin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Main Admin',
            'email' => 'mainadmin@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->secondAdmin = User::factory()->create([
            'name' => 'Second Admin',
            'email' => 'secondadmin@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'phone' => '9988776655',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_impersonate_any_user(): void
    {
        $response = $this->actingAs($this->customer)
            ->post("/admin/impersonate/{$this->secondAdmin->id}");

        $response->assertStatus(403);
    }

    public function test_guest_cannot_impersonate(): void
    {
        $response = $this->post("/admin/impersonate/{$this->customer->id}");

        $response->assertRedirect('/login');
    }

    public function test_admin_cannot_impersonate_self(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/impersonate/{$this->admin->id}");

        $response->assertSessionHas('error');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_admin_cannot_impersonate_another_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/impersonate/{$this->secondAdmin->id}");

        $response->assertSessionHas('error');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_admin_can_impersonate_customer_and_redirects_to_profile(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/impersonate/{$this->customer->id}?redirect=profile");

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('admin_impersonator_id', $this->admin->id);
        $response->assertSessionHas('admin_impersonator_name', $this->admin->name);

        $this->assertAuthenticatedAs($this->customer);
    }

    public function test_admin_can_impersonate_customer_and_redirects_to_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/impersonate/{$this->customer->id}?redirect=dashboard");

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->customer);
    }

    public function test_impersonated_user_can_exit_impersonation_and_return_to_admin(): void
    {
        // 1. Admin logs in as customer
        $this->actingAs($this->admin)
            ->post("/admin/impersonate/{$this->customer->id}");

        $this->assertAuthenticatedAs($this->customer);

        // 2. Customer exits impersonation
        $response = $this->post(route('impersonate.leave'));

        $response->assertRedirect();
        $response->assertSessionMissing('admin_impersonator_id');

        // 3. Admin session is restored
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_customer_lookup_view_contains_login_as_user_buttons(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.customer-lookup.index', ['user_id' => $this->customer->id]));

        $response->assertStatus(200);
        $response->assertSee('Login as User');
        $response->assertSee('LOGIN AS USER');
        $response->assertSee('/admin/impersonate');
    }

    public function test_user_show_view_contains_login_as_user_button(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $this->customer->id));

        $response->assertStatus(200);
        $response->assertSee('Login as User');
        $response->assertSee('/admin/impersonate');
    }

    public function test_impersonation_banner_is_displayed_when_impersonating(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/impersonate/{$this->customer->id}");

        $response = $this->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Admin Impersonation Mode');
        $response->assertSee('Exit & Return to Admin', false);
        $response->assertSee(route('impersonate.leave'));
    }
}
