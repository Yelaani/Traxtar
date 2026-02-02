<?php
require_once BASE_PATH . '/Database.php';

class Admin
{
    public static function findByEmail(string $email) {
        $db = Database::pdo();
        $sql = "SELECT id, name, email, password 
                FROM admins 
                WHERE LOWER(email) = LOWER(?) 
                LIMIT 1";
        $st = $db->prepare($sql);
        $st->execute([$email]);
        return $st->fetch();
    }

    public static function updatePassword(int $id, string $hash): bool {
        $db = Database::pdo();
        $st = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
        return $st->execute([$hash, $id]);
    }

    public static function create(array $data) {
        $db = Database::pdo(); // Changed from global $pdo to Database::pdo()
        try {
            $stmt = $db->prepare("INSERT INTO admins (name, email, password) VALUES (:name, :email, :password)");
            $result = $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'] ?? '',
            ]);
            if (!$result) {
                error_log("Admin create failed: " . print_r($stmt->errorInfo(), true));
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Admin create error: " . $e->getMessage());
            return false;
        }
    }
}