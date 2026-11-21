<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShopViewToggleTest extends TestCase
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
            'name' => 'John Customer',
            'email' => 'john@test.com',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_access_admin_shop(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.shop.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_shop_with_grid_and_list_toggle(): void
    {
        $product = Product::create([
            'name' => 'Super Grocery Kit',
            'price' => 599.00,
            'bv' => 30,
            'stock' => 50,
            'status' => 'active',
        ]);

        $package = Package::create([
            'name' => 'Mega Starter Pack',
            'price' => 1999.00,
            'bv' => 100,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.shop.index'));

        $response->assertStatus(200);

        // Verify Alpine toggle setup
        $response->assertSee('viewMode: localStorage.getItem(\'admin_shop_view\')', false);
        $response->assertSee('viewMode = \'grid\'', false);
        $response->assertSee('viewMode = \'list\'', false);

        // Verify toggle buttons exist
        $response->assertSee('Grid');
        $response->assertSee('List');

        // Verify both grid and list sections exist for products and packages
        $response->assertSee('Super Grocery Kit');
        $response->assertSee('Mega Starter Pack');
        $response->assertSee('x-show="viewMode === \'grid\'"', false);
        $response->assertSee('x-show="viewMode === \'list\'"', false);

        // Verify order action links exist
        $response->assertSee(route('admin.shop.checkout', ['product_id' => $product->id]));
        $response->assertSee(route('admin.shop.checkout', ['package_id' => $package->id]));
    }
}
