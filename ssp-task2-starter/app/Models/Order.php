<?php
require_once __DIR__ . '/../Database.php';

class Order {
  public static function create($user_id, $name, $phone, $address, $total, $status='pending') {
    $sql = "INSERT INTO orders (user_id, shipping_name, shipping_phone, shipping_address, total, status)
            VALUES (?,?,?,?,?,?)";
    $st = Database::pdo()->prepare($sql);
    $st->execute([(int)$user_id, $name, $phone, $address, (float)$total, $status]);
    return (int)Database::pdo()->lastInsertId();
  }

  public static function addItem($order_id, $product_id, $product_name, $price, $qty) {
    $st = Database::pdo()->prepare(
      "INSERT INTO order_items (order_id, product_id, product_name, price, qty) VALUES (?,?,?,?,?)"
    );
    $st->execute([(int)$order_id, (int)$product_id, $product_name, (float)$price, (int)$qty]);
  }

  public static function forUser($user_id) {
    $st = Database::pdo()->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC");
    $st->execute([(int)$user_id]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function findWithItems($id) {
    $pdo = Database::pdo();

    $st = $pdo->prepare("SELECT * FROM orders WHERE id=? LIMIT 1");
    $st->execute([(int)$id]);
    $order = $st->fetch(PDO::FETCH_ASSOC);
    if (!$order) return null;

    $it = $pdo->prepare("SELECT * FROM order_items WHERE order_id=? ORDER BY id");
    $it->execute([(int)$id]);
    $order['items'] = $it->fetchAll(PDO::FETCH_ASSOC);

    return $order;
  }
}
