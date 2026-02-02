<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireComponentTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        $this->product = Product::factory()->create([
            'stock' => 100,
            'price' => 50.00,
        ]);
    }

    /** @test */
    public function cart_component_can_be_rendered()
    {
        session(['cart' => [
            $this->product->id => [
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 2,
            ],
        ]]);

        $this->actingAs($this->customer)
            ->get(route('cart.index'))
            ->assertStatus(200);
    }

    /** @test */
    public function cart_component_can_update_quantity()
    {
        session(['cart' => [
            $this->product->id => [
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 2,
            ],
        ]]);

        Livewire::actingAs($this->customer)
            ->test(\App\Livewire\Cart::class)
            ->call('updateQuantity', $this->product->id, 5)
            ->assertSet('items.0.qty', 5);
    }

    /** @test */
    public function cart_component_can_remove_item()
    {
        session(['cart' => [
            $this->product->id => [
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 2,
            ],
        ]]);

        Livewire::actingAs($this->customer)
            ->test(\App\Livewire\Cart::class)
            ->call('removeItem', $this->product->id)
            ->assertCount('items', 0);
    }

    /** @test */
    public function cart_counter_updates_when_cart_changes()
    {
        Livewire::actingAs($this->customer)
            ->test(\App\Livewire\CartCounter::class)
            ->assertSet('count', 0);

        session(['cart' => [
            $this->product->id => [
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 3,
            ],
        ]]);

        Livewire::actingAs($this->customer)
            ->test(\App\Livewire\CartCounter::class)
            ->call('updateCount')
            ->assertSet('count', 3);
    }

    /** @test */
    public function product_shop_component_can_search()
    {
        Product::factory()->create(['name' => 'Laptop']);
        Product::factory()->create(['name' => 'Mouse']);
        Product::factory()->create(['name' => 'Keyboard']);

        Livewire::test(\App\Livewire\ProductShop::class)
            ->set('search', 'Laptop')
            ->assertSee('Laptop')
            ->assertDontSee('Mouse')
            ->assertDontSee('Keyboard');
    }

    /** @test */
    public function product_shop_component_can_sort()
    {
        Product::factory()->create(['name' => 'A Product', 'price' => 100]);
        Product::factory()->create(['name' => 'B Product', 'price' => 50]);
        Product::factory()->create(['name' => 'C Product', 'price' => 75]);

        Livewire::test(\App\Livewire\ProductShop::class)
            ->set('sortBy', 'price_low')
            ->assertSeeInOrder(['B Product', 'C Product', 'A Product']);
    }

    /** @test */
    public function product_list_component_can_delete_product()
    {
        $product = Product::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\ProductList::class)
            ->call('deleteProduct', $product->id)
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function product_form_component_can_create_product()
    {
        $productData = [
            'name' => 'New Product',
            'sku' => 'NEW-001',
            'description' => 'Test description',
            'price' => 99.99,
            'stock' => 50,
        ];

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\ProductForm::class)
            ->set('name', $productData['name'])
            ->set('sku', $productData['sku'])
            ->set('price', $productData['price'])
            ->set('stock', $productData['stock'])
            ->call('save')
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'sku' => 'NEW-001',
        ]);
    }

    /** @test */
    public function product_form_component_validates_required_fields()
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\ProductForm::class)
            ->call('save')
            ->assertHasErrors(['name', 'price', 'stock']);
    }
}
