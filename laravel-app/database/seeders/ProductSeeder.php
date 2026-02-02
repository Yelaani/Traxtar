<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing products to ensure we use the correct sportswear products
        // Delete all existing products first
        Product::query()->delete();

        $products = [
            [
                'name' => 'Women\'s Seamless Leggings',
                'sku' => 'AW-W-001',
                'description' => 'High-rise seamless leggings for training and yoga.',
                'price' => 39.90,
                'stock' => 48,
                'category_id' => 1,
                'image' => 'images/Women\'s Seamless Leggings.jpg',
            ],
            [
                'name' => 'Women\'s Medium-Support Sports Bra',
                'sku' => 'AW-W-002',
                'description' => 'Breathable sports bra with removable pads.',
                'price' => 24.50,
                'stock' => 36,
                'category_id' => 1,
                'image' => 'images/Women\'s Medium-Support Sports Bra.jpg',
            ],
            [
                'name' => 'Women\'s Airflow Tank',
                'sku' => 'AW-W-003',
                'description' => 'Featherlight tank with perforated back panel.',
                'price' => 19.90,
                'stock' => 52,
                'category_id' => 1,
                'image' => 'images/Women\'s Airflow Tank.jpg',
            ],
            [
                'name' => 'Women\'s Sprint 3" Shorts',
                'sku' => 'AW-W-004',
                'description' => 'Quick-dry running shorts with inner brief.',
                'price' => 22.00,
                'stock' => 41,
                'category_id' => 1,
                'image' => 'images/Women\'s Sprint 3 Shorts.jpg',
            ],
            [
                'name' => 'Women\'s Zip Hoodie',
                'sku' => 'AW-W-005',
                'description' => 'Soft terry zip hoodie for warm-ups.',
                'price' => 44.00,
                'stock' => 29,
                'category_id' => 1,
                'image' => 'images/Women\'s Zip Hoodie.jpg',
            ],
            [
                'name' => 'Women\'s Long-Sleeve Tech Tee',
                'sku' => 'AW-W-006',
                'description' => 'Moisture-wicking LS tee with thumbholes.',
                'price' => 27.50,
                'stock' => 33,
                'category_id' => 1,
                'image' => 'images/Women\'s Long-Sleeve Tech Tee.webp',
            ],
            [
                'name' => 'Women\'s Packable Windbreaker',
                'sku' => 'AW-W-007',
                'description' => 'Water-resistant, packs into chest pocket.',
                'price' => 49.00,
                'stock' => 25,
                'category_id' => 1,
                'image' => 'images/Women\'s Packable Windbreaker.jpg',
            ],
            [
                'name' => 'Men\'s Flex Training Shorts 7"',
                'sku' => 'AW-M-001',
                'description' => 'Stretch shorts with zip pocket.',
                'price' => 24.90,
                'stock' => 47,
                'category_id' => 2,
                'image' => 'images/Men\'s Flex Training Shorts 7.jpg',
            ],
            [
                'name' => 'Men\'s Performance Tee',
                'sku' => 'AW-M-002',
                'description' => 'Anti-odor knit for everyday training.',
                'price' => 18.90,
                'stock' => 62,
                'category_id' => 2,
                'image' => 'images/Men\'s Performance Tee.webp',
            ],
            [
                'name' => 'Men\'s Compression Top',
                'sku' => 'AW-M-003',
                'description' => 'Second-skin base layer for support.',
                'price' => 26.00,
                'stock' => 34,
                'category_id' => 2,
                'image' => 'images/Men\'s Compression Top.jpg',
            ],
            [
                'name' => 'Men\'s Tapered Joggers',
                'sku' => 'AW-M-004',
                'description' => 'Brushed interior, ankle zips.',
                'price' => 39.00,
                'stock' => 28,
                'category_id' => 2,
                'image' => 'images/Men\'s Tapered Joggers.jpg',
            ],
            [
                'name' => 'Men\'s Fleece Hoodie',
                'sku' => 'AW-M-005',
                'description' => 'Midweight fleece with kangaroo pocket.',
                'price' => 42.00,
                'stock' => 31,
                'category_id' => 2,
                'image' => 'images/Men\'s Fleece Hoodie.jpg',
            ],
            [
                'name' => 'Men\'s Court Polo',
                'sku' => 'AW-M-006',
                'description' => 'Breathable knit polo for sport & casual.',
                'price' => 29.50,
                'stock' => 26,
                'category_id' => 2,
                'image' => 'images/Men\'s Court Polo.jpg',
            ],
            [
                'name' => 'Men\'s Run Tights',
                'sku' => 'AW-M-007',
                'description' => 'Reflective details, phone pocket.',
                'price' => 34.90,
                'stock' => 24,
                'category_id' => 2,
                'image' => 'images/Men\'s Run Tights.jpg',
            ],
            [
                'name' => 'Insulated Water Bottle 750ml',
                'sku' => 'AW-A-001',
                'description' => 'Double-wall stainless steel bottle.',
                'price' => 16.00,
                'stock' => 58,
                'category_id' => 3,
                'image' => 'images/Insulated Water Bottle 750ml.jpg',
            ],
            [
                'name' => 'Aero Cap',
                'sku' => 'AW-A-002',
                'description' => 'Lightweight cap with mesh panels.',
                'price' => 14.50,
                'stock' => 45,
                'category_id' => 3,
                'image' => 'images/Aero Cap.jpg',
            ],
            [
                'name' => 'Cushion Crew Socks 3-Pack',
                'sku' => 'AW-A-003',
                'description' => 'Arch support and breathable mesh.',
                'price' => 12.90,
                'stock' => 73,
                'category_id' => 3,
                'image' => 'images/Cushion Crew Socks 3-Pack.webp',
            ],
            [
                'name' => 'Convertible Gym Bag 35L',
                'sku' => 'AW-A-004',
                'description' => 'Duffel-to-backpack with shoe garage.',
                'price' => 39.90,
                'stock' => 27,
                'category_id' => 3,
                'image' => 'images/Convertible Gym Bag 35L.jpg',
            ],
            [
                'name' => 'Grip Yoga Mat 5mm',
                'sku' => 'AW-A-005',
                'description' => 'Non-slip TPE surface, carry strap.',
                'price' => 24.00,
                'stock' => 32,
                'category_id' => 3,
                'image' => 'images/Grip Yoga Mat 5mm.jpg',
            ],
            [
                'name' => 'Sweat Wristbands 2-Pack',
                'sku' => 'AW-A-006',
                'description' => 'Absorbent terry wristbands.',
                'price' => 8.50,
                'stock' => 66,
                'category_id' => 3,
                'image' => 'images/Sweat Wristbands 2-Pack.jpg',
            ],
            [
                'name' => 'Traxtar E-Gift Card - 2000',
                'sku' => 'AW-G-001',
                'description' => 'Digital gift card worth LKR 2,000. Perfect for gifting to friends and family.',
                'price' => 2000.00,
                'stock' => 999,
                'category_id' => 4,
                'image' => 'images/Traxtar E-Gift Card.png',
            ],
            [
                'name' => 'Traxtar E-Gift Card - 5000',
                'sku' => 'AW-G-002',
                'description' => 'Digital gift card worth LKR 5,000. Perfect for gifting to friends and family.',
                'price' => 5000.00,
                'stock' => 999,
                'category_id' => 4,
                'image' => 'images/Traxtar E-Gift Card.png',
            ],
            [
                'name' => 'Traxtar E-Gift Card - 10000',
                'sku' => 'AW-G-003',
                'description' => 'Digital gift card worth LKR 10,000. Perfect for gifting to friends and family.',
                'price' => 10000.00,
                'stock' => 999,
                'category_id' => 4,
                'image' => 'images/Traxtar E-Gift Card.png',
            ],
        ];

        foreach ($products as $product) {
            // Ensure products are created with 'active' status
            Product::create(array_merge($product, ['status' => 'active']));
        }

        $this->command->info('Created ' . count($products) . ' sample products.');
    }
}
