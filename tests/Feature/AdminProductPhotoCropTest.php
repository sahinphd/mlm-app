<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminProductPhotoCropTest extends TestCase
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

    public function test_admin_can_view_product_edit_with_photo_cropper(): void
    {
        $product = Product::create([
            'name' => 'Organic Honey 500g',
            'price' => 350.00,
            'bv' => 20,
            'stock' => 25,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.edit', $product->id));

        $response->assertStatus(200);
        $response->assertSee('Edit Product: Organic Honey 500g');
        $response->assertSee('enctype="multipart/form-data"', false);
        $response->assertSee('product-image-file');
        $response->assertSee('avatarCropperModal');
        $response->assertSee('defaultTargetKB: 28', false);
        $response->assertSee('Max 30 KB');
        $response->assertSee('product-image-preview');
    }

    public function test_admin_can_update_product_with_uploaded_photo(): void
    {
        $product = Product::create([
            'name' => 'Cold Pressed Mustard Oil',
            'price' => 210.00,
            'bv' => 15,
            'stock' => 40,
            'status' => 'active',
        ]);

        // Create a fake image (e.g. 25 KB simulating client cropper output)
        $file = UploadedFile::fake()->image('mustard_oil.jpg', 400, 400)->size(25);

        $response = $this->actingAs($this->admin)->post(route('admin.products.update', $product->id), [
            'name' => 'Cold Pressed Mustard Oil 1L',
            'price' => 220.00,
            'bv' => 15,
            'stock' => 35,
            'status' => 'active',
            'image_file' => $file,
        ]);

        $response->assertRedirect(route('admin.products'));
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('Cold Pressed Mustard Oil 1L', $product->name);
        $this->assertNotNull($product->getRawOriginal('image'));
        $this->assertStringStartsWith('images/products/', $product->getRawOriginal('image'));

        // Check file exists in public/images/products
        $filePath = public_path($product->getRawOriginal('image'));
        $this->assertTrue(File::exists($filePath));

        // Clean up uploaded test file
        if (File::exists($filePath)) {
            @unlink($filePath);
        }
    }

    public function test_product_model_image_accessor_returns_url(): void
    {
        $product = Product::create([
            'name' => 'Turmeric Powder',
            'price' => 85.00,
            'bv' => 5,
            'stock' => 50,
            'status' => 'active',
            'image' => 'images/products/turmeric.jpg',
        ]);

        $this->assertEquals('images/products/turmeric.jpg', $product->getRawOriginal('image'));
        $this->assertEquals(asset('images/products/turmeric.jpg'), $product->image);
        $this->assertEquals(asset('images/products/turmeric.jpg'), $product->image_url);
    }
}
