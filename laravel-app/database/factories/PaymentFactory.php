<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'stripe_payment_intent_id' => 'pi_' . fake()->bothify('test_########'),
            'stripe_charge_id' => 'ch_' . fake()->bothify('test_########'),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'currency' => 'usd',
            'status' => fake()->randomElement(['pending', 'processing', 'succeeded', 'failed', 'cancelled']),
            'payment_method' => 'card',
            'failure_reason' => null,
            'metadata' => null,
        ];
    }
}
