<?php
require_once __DIR__ . '/../Database.php';

class Product {

    public static function all() {
        $stmt = Database::pdo()->query("
            SELECT id, name, sku, description, price, stock, category_id, image, created_at, updated_at
            FROM products
            ORDER BY id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function find($id) {
        $st = Database::pdo()->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $st->execute([(int)$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public static function create(array $d): int {
        $st = Database::pdo()->prepare(
            "INSERT INTO products (name, sku, description, price, stock, category_id, image)
             VALUES (?,?,?,?,?,?,?)"
        );
        $st->execute([
            $d['name'],
            $d['sku'] !== '' ? $d['sku'] : null,
            $d['description'] !== '' ? $d['description'] : null,
            $d['price'],
            $d['stock'],
            $d['category_id'],
            $d['image'],
        ]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update($id, array $d): bool {
        // build dynamic SET to avoid overwriting image if not provided
        $fields = ['name = ?', 'sku = ?', 'description = ?', 'price = ?', 'stock = ?', 'category_id = ?'];
        $params = [
            $d['name'],
            $d['sku'] !== '' ? $d['sku'] : null,
            $d['description'] !== '' ? $d['description'] : null,
            $d['price'],
            $d['stock'],
            $d['category_id'],
        ];
        if (array_key_exists('image', $d)) {
            $fields[] = 'image = ?';
            $params[] = $d['image'];
        }

        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $params[] = (int)$id;

        $st = Database::pdo()->prepare($sql);
        return $st->execute($params);
    }

    public static function delete($id): bool {
        $st = Database::pdo()->prepare("DELETE FROM products WHERE id = ?");
        return $st->execute([(int)$id]);
    }
}
