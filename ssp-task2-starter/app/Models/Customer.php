<?php
require_once BASE_PATH . 'Database.php';

class Customer
{
    public static function findByEmail(string $email) {
        $db = Database::pdo();
        $sql = "SELECT id, name, email, phone, address, password AS password_hash 
                FROM customers 
                WHERE LOWER(email) = LOWER(?) 
                LIMIT 1";
        $st = $db->prepare($sql);
        $st->execute([$email]);
        return $st->fetch();
    }

    public static function updatePassword(int $id, string $hash): bool {
        $db = Database::pdo();
        $st = $db->prepare("UPDATE customers SET password = ? WHERE id = ?");
        return $st->execute([$hash, $id]);
    }

    public static function create(array $data): int {
        $db = Database::pdo();
        $stmt = $db->prepare(
            "INSERT INTO customers (name, email, password, phone, address) 
             VALUES (:name, :email, :password, :phone, :address)"
        );
        $success = $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?? '',
            'phone' => $data['phone'] ?? '',
            'address' => $data['address'] ?? '',
        ]);
        if (!$success) {
            error_log("Customer create failed: " . print_r($stmt->errorInfo(), true));
        }
        return (int)$db->lastInsertId();
    }
}