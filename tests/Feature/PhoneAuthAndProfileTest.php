<?php

namespace Tests\Feature;

use App\Helpers\PhoneHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhoneAuthAndProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_helper_normalization_variants()
    {
        // 10-digit Indian phone
        $this->assertEquals('9876543210', PhoneHelper::normalize('9876543210'));
        $this->assertEquals('9876543210', PhoneHelper::normalize('+91 9876543210'));
        $this->assertEquals('9876543210', PhoneHelper::normalize('+919876543210'));
        $this->assertEquals('9876543210', PhoneHelper::normalize('09876543210'));
        $this->assertEquals('9876543210', PhoneHelper::normalize(' 98765 43210 '));
        $this->assertEquals('9876543210', PhoneHelper::normalize('+91 98765 43210', '+91'));

        // International phone
        $this->assertEquals('+8801712345678', PhoneHelper::normalize('1712345678', '+880'));
        $this->assertEquals('+8801712345678', PhoneHelper::normalize('+880 1712 345678', '+880'));
        $this->assertEquals('+15551234567', PhoneHelper::normalize('(555) 123-4567', '+1'));

        // Validation
        $this->assertTrue(PhoneHelper::isValid('9876543210'));
        $this->assertTrue(PhoneHelper::isValid('+8801712345678'));
        $this->assertFalse(PhoneHelper::isValid('12345'));
        $this->assertFalse(PhoneHelper::isValid(''));

        // Variants
        $variants = PhoneHelper::getPossibleVariants('+91 9876543210');
        $this->assertContains('9876543210', $variants);
        $this->assertContains('+91 9876543210', $variants);
        $this->assertContains('+919876543210', $variants);
        $this->assertContains('09876543210', $variants);
    }

    public function test_user_can_register_with_10_digit_phone_and_country_code()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'country' => '+91',
            'phone' => '9876543210',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'phone' => '9876543210',
        ]);
    }

    public function test_user_can_register_when_pasting_plus91_and_spaces()
    {
        $response = $this->post('/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'country' => '+91',
            'phone' => '+91 98765 43210',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(302);
        // Should be normalized to 10 digits without duplicate +91 or spaces
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'phone' => '9876543210',
        ]);
    }

    public function test_duplicate_phone_detected_regardless_of_format()
    {
        // Existing user has clean 10-digit phone
        User::create([
            'name' => 'Original User',
            'email' => 'original@example.com',
            'phone' => '9876543210',
            'password' => Hash::make('password123'),
        ]);

        // Trying to register with +91 9876543210
        $response1 = $this->from('/register')->post('/register', [
            'name' => 'Duplicate Attempt 1',
            'email' => 'dup1@example.com',
            'country' => '+91',
            'phone' => '+91 9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response1->assertRedirect('/register');
        $response1->assertSessionHasErrors(['phone']);

        // Trying to register with spaces 98765 43210
        $response2 = $this->from('/register')->post('/register', [
            'name' => 'Duplicate Attempt 2',
            'email' => 'dup2@example.com',
            'country' => '+91',
            'phone' => ' 98765 43210 ',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response2->assertRedirect('/register');
        $response2->assertSessionHasErrors(['phone']);
    }

    public function test_user_can_login_with_different_phone_formats()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '9876543210',
            'password' => Hash::make('password123'),
        ]);

        // Login with 10 digits
        $this->post('/login', [
            'email' => '9876543210',
            'password' => 'password123',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        // Login with +91 9876543210
        $this->post('/login', [
            'email' => '+91 9876543210',
            'password' => 'password123',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        // Login with +919876543210 (no space)
        $this->post('/login', [
            'email' => '+919876543210',
            'password' => 'password123',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        // Login with spaces
        $this->post('/login', [
            'email' => ' 98765 43210 ',
            'password' => 'password123',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        // Login with leading zero
        $this->post('/login', [
            'email' => '09876543210',
            'password' => 'password123',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_profile_update_normalizes_phone_and_strips_spaces()
    {
        $user = User::create([
            'name' => 'Profile User',
            'email' => 'profile@example.com',
            'phone' => '9876543210',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);

        // Update with +91 and spaces
        $response = $this->post('/profile', [
            'name' => 'Profile User Updated',
            'phone' => '+91 98123 45678',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertEquals('9812345678', $user->phone);

        // Update with pure spaces
        $response = $this->post('/profile', [
            'name' => 'Profile User Updated 2',
            'phone' => ' 98000 11222 ',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertEquals('9800011222', $user->phone);
    }

    public function test_profile_update_blocks_duplicate_phone()
    {
        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'phone' => '9876543210',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'phone' => '9123456789',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user2);

        // Try updating to user1's phone with +91 format
        $response = $this->post('/profile', [
            'name' => 'User Two',
            'phone' => '+91 98765 43210',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $user2->refresh();
        $this->assertEquals('9123456789', $user2->phone);
    }

    public function test_auth_ui_test_regression_without_phone_passes()
    {
        $resp = $this->post('/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $resp->assertStatus(302);
        $this->assertAuthenticated();
    }
}
