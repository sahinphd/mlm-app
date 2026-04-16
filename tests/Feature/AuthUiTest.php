<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_logout_and_admin_links()
    {
        // Register a new user
        $resp = $this->post('/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $resp->assertStatus(302);
        $this->assertAuthenticated();

        // After register, visiting root should redirect to /payments
        $this->get('/')->assertRedirect('/payments');

        // Logout
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        // Login with created user
        $this->post('/login', ['email' => 'alice@example.com', 'password' => 'password'])->assertRedirect('/payments');

        // Create an admin and ensure /admin is accessible
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);
        $this->actingAs($admin)->get('/admin')->assertStatus(200)->assertSee('Admin Dashboard');
    }
}
