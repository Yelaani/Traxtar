<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
        
        $this->product = Product::factory()->create([
            'stock' => 100,
            'price' => 50.00,
        ]);
        
        $this->order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 100.00,
        ]);
    }

    /** @test */
    public function customer_can_access_payment_checkout()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('payment.checkout', $this->order));

        // Should redirect to payment creation or show checkout page
        // Depending on implementation, this might redirect or show page
        $response->assertStatus(200);
    }

    /** @test */
    public function customer_cannot_access_other_customers_order_payment()
    {
        $otherCustomer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
        
        $otherOrder = Order::factory()->create([
            'user_id' => $otherCustomer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('payment.checkout', $otherOrder));

        $response->assertStatus(403);
    }

    /** @test */
    public function payment_cannot_be_created_for_already_paid_order()
    {
        Payment::factory()->create([
            'order_id' => $this->order->id,
            'status' => 'succeeded',
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('payment.create'), [
                'order_id' => $this->order->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function payment_requires_valid_order()
    {
        $response = $this->actingAs($this->customer)
            ->post(route('payment.create'), [
                'order_id' => 99999, // Non-existent order
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function payment_success_route_requires_valid_payment_intent()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('payment.success', [
                'payment_intent' => 'pi_test_invalid',
                'order_id' => $this->order->id,
            ]));

        // Should handle invalid payment intent gracefully
        $response->assertStatus(200);
    }

    /** @test */
    public function payment_cancel_route_works()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('payment.cancel', $this->order));

        $response->assertRedirect(route('orders.show', $this->order));
    }

    /** @test */
    public function order_shows_payment_status()
    {
        Payment::factory()->create([
            'order_id' => $this->order->id,
            'status' => 'succeeded',
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('orders.show', $this->order));

        $response->assertStatus(200);
        $response->assertViewHas('hasSuccessfulPayment', true);
    }

    /** @test */
    public function order_shows_failed_payment_status()
    {
        Payment::factory()->create([
            'order_id' => $this->order->id,
            'status' => 'failed',
            'failure_reason' => 'Card declined',
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('orders.show', $this->order));

        $response->assertStatus(200);
        $response->assertViewHas('hasFailedPayment', true);
    }

    /** @test */
    public function only_pending_orders_can_be_paid()
    {
        $completedOrder = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'delivered',
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('payment.create'), [
                'order_id' => $completedOrder->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
