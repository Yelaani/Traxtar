<?php
/**
 * Script to export products from the original Traxtar database
 * Run this BEFORE the Laravel migration runs, or if you have a backup
 */

// Original app database config
$config = require __DIR__ . '/../ssp-task2-starter/app/config.php';
$dbConfig = $config['db'];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}",
        $dbConfig['user'],
        $dbConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Check if products table exists in original structure
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() === 0) {
        echo "Products table does not exist in original database.\n";
        exit(1);
    }
    
    // Try to get products (might have different structure)
    $stmt = $pdo->query("SELECT * FROM products LIMIT 1");
    $columns = [];
    for ($i = 0; $i < $stmt->columnCount(); $i++) {
        $col = $stmt->getColumnMeta($i);
        $columns[] = $col['name'];
    }
    
    echo "Found products table with columns: " . implode(', ', $columns) . "\n";
    
    // Get all products
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($products) . " products.\n";
    
    if (count($products) > 0) {
        // Save to JSON file
        file_put_contents(__DIR__ . '/products_backup.json', json_encode($products, JSON_PRETTY_PRINT));
        echo "Products exported to products_backup.json\n";
        
        // Also create SQL insert statements
        $sql = "-- Products export\n";
        $sql .= "INSERT INTO products (id, name, sku, description, price, stock, category_id, image, created_at, updated_at) VALUES\n";
        
        $values = [];
        foreach ($products as $product) {
            $vals = [];
            $vals[] = $product['id'] ?? 'NULL';
            $vals[] = $pdo->quote($product['name'] ?? '');
            $vals[] = isset($product['sku']) && $product['sku'] !== null ? $pdo->quote($product['sku']) : 'NULL';
            $vals[] = isset($product['description']) && $product['description'] !== null ? $pdo->quote($product['description']) : 'NULL';
            $vals[] = $product['price'] ?? 0;
            $vals[] = $product['stock'] ?? 0;
            $vals[] = isset($product['category_id']) && $product['category_id'] !== null ? $product['category_id'] : 'NULL';
            $vals[] = isset($product['image']) && $product['image'] !== null ? $pdo->quote($product['image']) : 'NULL';
            $vals[] = isset($product['created_at']) && $product['created_at'] !== null ? $pdo->quote($product['created_at']) : 'NULL';
            $vals[] = isset($product['updated_at']) && $product['updated_at'] !== null ? $pdo->quote($product['updated_at']) : 'NULL';
            
            $values[] = '(' . implode(', ', $vals) . ')';
        }
        
        $sql .= implode(",\n", $values) . ";\n";
        
        file_put_contents(__DIR__ . '/products_backup.sql', $sql);
        echo "SQL export saved to products_backup.sql\n";
    }
    
} catch (PDOException $e) {
    echo "Error connecting to database: " . $e->getMessage() . "\n";
    echo "Products may have already been migrated or the table structure is different.\n";
}
