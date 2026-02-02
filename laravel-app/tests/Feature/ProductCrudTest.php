<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        // Create customer user
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function admin_can_view_product_list()
    {
        Product::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    /** @test */
    public function customer_cannot_access_admin_product_list()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.products.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_admin_product_list()
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_view_create_product_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('products.create');
    }

    /** @test */
    public function admin_can_create_product()
    {
        Storage::fake('public');

        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'description' => 'Test description',
            'price' => 99.99,
            'stock' => 50,
            'image' => UploadedFile::fake()->image('product.jpg'),
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $productData);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'stock' => 50,
        ]);
    }

    /** @test */
    public function admin_can_create_product_without_image()
    {
        $productData = [
            'name' => 'Test Product No Image',
            'price' => 49.99,
            'stock' => 25,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $productData);

        $response->assertRedirect(route('admin.products.index'));
        
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product No Image',
            'price' => 49.99,
        ]);
    }

    /** @test */
    public function product_creation_requires_valid_data()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), []);

        $response->assertSessionHasErrors(['name', 'price', 'stock']);
    }

    /** @test */
    public function admin_can_view_edit_product_form()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('products.edit');
    }

    /** @test */
    public function admin_can_update_product()
    {
        Storage::fake('public');
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 50.00,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'price' => 75.00,
            'stock' => 100,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $updateData);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 75.00,
            'stock' => 100,
        ]);
    }

    /** @test */
    public function admin_can_update_product_image()
    {
        Storage::fake('public');
        $product = Product::factory()->create([
            'image' => 'uploads/old-image.jpg',
        ]);

        $newImage = UploadedFile::fake()->image('new-product.jpg');

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'image' => $newImage,
            ]);

        $response->assertRedirect(route('admin.products.index'));
        
        $product->refresh();
        $this->assertNotNull($product->image);
        $this->assertNotEquals('uploads/old-image.jpg', $product->image);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        Storage::fake('public');
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function customer_cannot_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->customer)
            ->delete(route('admin.products.destroy', $product));

        $response->assertStatus(403);
    }

    /** @test */
    public function public_can_view_product_shop()
    {
        Product::factory()->count(5)->create();

        $response = $this->get(route('products.shop'));

        $response->assertStatus(200);
        $response->assertViewIs('products.shop');
    }

    /** @test */
    public function public_can_view_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertViewHas('product', $product);
    }
}
