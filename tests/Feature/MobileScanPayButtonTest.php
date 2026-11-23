<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileScanPayButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_mobile_scan_and_pay_button()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'role' => 'user',
        ]);
        Wallet::create([
            'user_id' => $user->id,
            'main_balance' => 500,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('phonepe-fab-btn');
        $response->assertSee('Instant QR Payment');
        $response->assertSee(route('wallet.scan-pay'));
        $response->assertSee('phonepe-scan-laser-line');
        $response->assertSee('md:hidden');
    }

    public function test_scan_and_pay_route_is_accessible()
    {
        $user = User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'testuser2@example.com',
            'role' => 'user',
        ]);
        Wallet::create([
            'user_id' => $user->id,
            'main_balance' => 1000,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('wallet.scan-pay'));
        $response->assertStatus(200);
        $response->assertSee('Scan QR');
    }
}
