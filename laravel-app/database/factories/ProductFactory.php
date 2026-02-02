<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $genders = ['Men\'s', 'Women\'s'];
        $clothingTypes = [
            'T-Shirt', 'Performance Tee', 'Tank Top', 'Long-Sleeve Tee',
            'Shorts', 'Running Shorts', 'Training Shorts', 'Leggings',
            'Joggers', 'Hoodie', 'Zip Hoodie', 'Jacket', 'Windbreaker',
            'Sports Bra', 'Compression Top', 'Polo Shirt'
        ];
        $accessories = [
            'Sports Cap', 'Baseball Cap', 'Gym Bag', 'Backpack',
            'Water Bottle', 'Socks', 'Crew Socks', 'Ankle Socks',
            'Wristbands', 'Headband', 'Gym Towel', 'Yoga Mat'
        ];
        
        $isAccessory = fake()->boolean(30); // 30% chance of being an accessory
        
        if ($isAccessory) {
            $name = fake()->randomElement($accessories);
            $descriptions = [
                'Essential sports accessory for your active lifestyle.',
                'High-quality sports accessory designed for performance.',
                'Durable and functional sports accessory.',
                'Perfect addition to your sports gear collection.',
            ];
        } else {
            $gender = fake()->randomElement($genders);
            $type = fake()->randomElement($clothingTypes);
            $name = $gender . ' ' . $type;
            $descriptions = [
                'Moisture-wicking fabric for maximum comfort during workouts.',
                'Breathable and lightweight design perfect for training.',
                'High-performance sportswear designed for active athletes.',
                'Comfortable and durable sportswear for all your activities.',
                'Premium quality sportswear with excellent fit and feel.',
            ];
        }
        
        $descriptors = [
            'moisture-wicking', 'breathable', 'lightweight', 'durable',
            'high-performance', 'compression', 'quick-dry', 'stretch',
            'anti-odor', 'reflective', 'water-resistant', 'seamless'
        ];
        
        $description = fake()->randomElement($descriptions) . ' ' . 
                      fake()->randomElement($descriptors) . ' material.';
        
        return [
            'name' => $name,
            'sku' => fake()->unique()->bothify('SW-####'),
            'description' => ucfirst($description),
            'price' => fake()->randomFloat(2, 8, 60),
            'stock' => fake()->numberBetween(0, 100),
            'category_id' => null,
            'image' => null,
        ];
    }
}
