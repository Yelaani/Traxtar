<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $otherCustomer;
    private User $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
        
        $this->otherCustomer = User::factory()->create([
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
    public function customer_can_place_order()
    {
        // Add product to cart
        session(['cart' => [
            $this->product->id => [
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 2,
            ],
        ]]);

        $orderData = [
            'shipping_name' => 'John Doe',
            'shipping_phone' => '1234567890',
            'shipping_address' => '123 Test Street',
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('orders.place'), $orderData);

        $response->assertRedirect(route('payment.create'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'shipping_name' => 'John Doe',
            'status' => 'pending',
        ]);
        
        // Verify stock was decremented
        $this->product->refresh();
        $this->assertEquals(98, $this->product->stock);
    }

    /** @test */
    public function order_creation_requires_valid_shipping_data()
    {
        session(['cart' => [
            $this->product->id => [
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 1,
            ],
        ]]);

        $response = $this->actingAs($this->customer)
            ->post(route('orders.place'), []);

        $response->assertSessionHasErrors(['shipping_name', 'shipping_phone', 'shipping_address']);
    }

    /** @test */
    public function cannot_place_order_with_empty_cart()
    {
        $orderData = [
            'shipping_name' => 'John Doe',
            'shipping_phone' => '1234567890',
            'shipping_address' => '123 Test Street',
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('orders.place'), $orderData);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function cannot_place_order_with_insufficient_stock()
    {
        $lowStockProduct = Product::factory()->create([
            'stock' => 5,
        ]);

        session(['cart' => [
            $lowStockProduct->id => [
                'name' => $lowStockProduct->name,
                'price' => $lowStockProduct->price,
                'qty' => 10, // More than available
            ],
        ]]);

        $orderData = [
            'shipping_name' => 'John Doe',
            'shipping_phone' => '1234567890',
            'shipping_address' => '123 Test Street',
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('orders.place'), $orderData);

        $response->assertRedirect(route('cart.checkout'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function customer_can_view_their_orders()
    {
        Order::factory()->count(3)->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('orders.index');
    }

    /** @test */
    public function customer_can_view_their_specific_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('orders.show', $order));

        $response->assertStatus(200);
        $response->assertViewIs('orders.show');
        $response->assertViewHas('order', $order);
    }

    /** @test */
    public function customer_cannot_view_other_customers_orders()
    {
        $order = Order::factory()->create([
            'user_id' => $this->otherCustomer->id,
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('orders.show', $order));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_any_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('orders.show', $order));

        $response->assertStatus(200);
        $response->assertViewIs('orders.show');
    }

    /** @test */
    public function order_contains_correct_items()
    {
        $product2 = Product::factory()->create([
            'stock' => 50,
            'price' => 25.00,
        ]);

        session(['cart' => [
            $this->product->id => [
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 2,
            ],
            $product2->id => [
                'name' => $product2->name,
                'price' => $product2->price,
                'qty' => 3,
            ],
        ]]);

        $orderData = [
            'shipping_name' => 'John Doe',
            'shipping_phone' => '1234567890',
            'shipping_address' => '123 Test Street',
        ];

        $this->actingAs($this->customer)
            ->post(route('orders.place'), $orderData);

        $order = Order::where('user_id', $this->customer->id)->first();
        
        $this->assertNotNull($order);
        $this->assertEquals(2, $order->items->count());
        $this->assertEquals(175.00, $order->total); // (50 * 2) + (25 * 3)
    }
}
