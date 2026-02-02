<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ApiCartTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private string $customerToken;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->customerToken = $this->customer->createToken('api-token')->plainTextToken;
        $this->product = Product::factory()->create(['stock' => 100]);
    }

    /** @test */
    public function authenticated_user_can_view_cart_via_api()
    {
        // Add item to cart via cache (for API requests)
        $cartKey = "api_cart_user_{$this->customer->id}";
        Cache::put($cartKey, [
            $this->product->id => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 2,
            ],
        ], now()->addDays(7));

        $response = $this->withHeader('Authorization', "Bearer {$this->customerToken}")
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => ['id', 'name', 'price', 'qty', 'subtotal'],
                    ],
                    'total',
                ],
            ]);
    }

    /** @test */
    public function authenticated_user_can_add_item_to_cart_via_api()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->customerToken}")
            ->postJson('/api/cart', [
                'product_id' => $this->product->id,
                'qty' => 3,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['items', 'total'],
            ]);

        // Verify cart is stored in cache for API requests
        $cartKey = "api_cart_user_{$this->customer->id}";
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has($cartKey));
    }

    /** @test */
    public function cannot_add_item_with_insufficient_stock_via_api()
    {
        $lowStockProduct = Product::factory()->create(['stock' => 5]);

        $response = $this->withHeader('Authorization', "Bearer {$this->customerToken}")
            ->postJson('/api/cart', [
                'product_id' => $lowStockProduct->id,
                'qty' => 10,
            ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function authenticated_user_can_update_cart_item_via_api()
    {
        // Add item to cart via cache (for API requests)
        $cartKey = "api_cart_user_{$this->customer->id}";
        Cache::put($cartKey, [
            $this->product->id => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 2,
            ],
        ], now()->addDays(7));

        $response = $this->withHeader('Authorization', "Bearer {$this->customerToken}")
            ->putJson("/api/cart/{$this->product->id}", [
                'qty' => 5,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $cart = Cache::get($cartKey, []);
        $this->assertEquals(5, $cart[$this->product->id]['qty']);
    }

    /** @test */
    public function authenticated_user_can_remove_item_from_cart_via_api()
    {
        // Add item to cart via cache (for API requests)
        $cartKey = "api_cart_user_{$this->customer->id}";
        Cache::put($cartKey, [
            $this->product->id => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
                'qty' => 2,
            ],
        ], now()->addDays(7));

        $response = $this->withHeader('Authorization', "Bearer {$this->customerToken}")
            ->deleteJson("/api/cart/{$this->product->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $cart = Cache::get($cartKey, []);
        $this->assertArrayNotHasKey($this->product->id, $cart);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_cart_via_api()
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    }
}
