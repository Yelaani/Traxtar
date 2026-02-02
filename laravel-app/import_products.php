<?php
/**
 * Script to import products from backup into Laravel
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check if backup files exist
$jsonFile = __DIR__ . '/products_backup.json';
$sqlFile = __DIR__ . '/products_backup.sql';

if (file_exists($jsonFile)) {
    echo "Importing from JSON backup...\n";
    $products = json_decode(file_get_contents($jsonFile), true);
    
    $imported = 0;
    foreach ($products as $product) {
        try {
            \App\Models\Product::updateOrCreate(
                ['id' => $product['id']],
                [
                    'name' => $product['name'] ?? '',
                    'sku' => $product['sku'] ?? null,
                    'description' => $product['description'] ?? null,
                    'price' => $product['price'] ?? 0,
                    'stock' => $product['stock'] ?? 0,
                    'category_id' => $product['category_id'] ?? null,
                    'image' => $product['image'] ?? null,
                    'created_at' => $product['created_at'] ?? now(),
                    'updated_at' => $product['updated_at'] ?? now(),
                ]
            );
            $imported++;
        } catch (Exception $e) {
            echo "Error importing product ID {$product['id']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "Imported $imported products.\n";
    
} elseif (file_exists($sqlFile)) {
    echo "Importing from SQL backup...\n";
    $sql = file_get_contents($sqlFile);
    \Illuminate\Support\Facades\DB::unprepared($sql);
    echo "SQL import completed.\n";
    
} else {
    echo "No backup files found (products_backup.json or products_backup.sql).\n";
    echo "Please run export_products_from_original.php first, or provide a backup file.\n";
    exit(1);
}

$count = \App\Models\Product::count();
echo "Total products in database now: $count\n";
