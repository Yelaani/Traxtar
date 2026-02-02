<?php

require_once __DIR__ . '/../Views/helpers.php';
require_once BASE_PATH . 'Models/Product.php';
require_once BASE_PATH . 'Models/Order.php';

class OrderController {

  public function cart() {
    $items = cart_items();
    $total = cart_total();
    view('cart/index', compact('items', 'total'));
  }

  public function updateCart() {
    require_auth();
    verify_csrf();

    if (current_user()['role'] !== 'customer') {
      $_SESSION['error'] = 'Only customers can modify the cart.';
      redirect('dashboard');
    }

    $items = cart_items();
    if (empty($items)) {
      $_SESSION['cart_message'] = 'Cart is empty.';
      redirect('cart');
    }

    // Handle clear cart
    if (isset($_POST['clear'])) {
      $_SESSION['cart'] = [];
      $_SESSION['cart_message'] = 'Cart cleared.';
      redirect('cart');
    }

    // Handle remove item
    if (isset($_POST['remove']) && is_numeric($_POST['remove'])) {
      $product_id = (int)$_POST['remove'];
      $new_items = array_filter($items, fn($item) => $item['id'] != $product_id);
      $_SESSION['cart'] = array_values($new_items);
      $_SESSION['cart_message'] = 'Item removed.';
      redirect('cart');
    }

    // Handle update quantities
    if (isset($_POST['update']) && isset($_POST['qty']) && is_array($_POST['qty'])) {
      $new_items = [];
      $error = null;

      foreach ($items as $item) {
        $product_id = (int)$item['id'];
        $qty = isset($_POST['qty'][$product_id]) ? (int)$_POST['qty'][$product_id] : $item['qty'];

        if ($qty <= 0) continue; // Auto-remove if quantity is 0

        $product = Product::find($product_id);
        if (!$product) {
          $error = "Product {$item['name']} not found.";
          break;
        }
        if ($product['stock'] < $qty) {
          $error = "Insufficient stock for {$item['name']} (available: {$product['stock']}).";
          break;
        }

        $item['qty'] = $qty;
        $new_items[] = $item;
      }

      if ($error) {
        view('cart/index', ['items' => $items, 'total' => cart_total(), 'error' => $error]);
      } else {
        $_SESSION['cart'] = $new_items;
        $_SESSION['cart_message'] = 'Cart updated.';
        redirect('cart');
      }
    }

    redirect('cart');
  }

  public function checkoutForm() {
    require_auth();
    if (current_user()['role'] !== 'customer') {
      $_SESSION['error'] = 'Only customers can place orders.';
      redirect('dashboard');
    }
    if (!cart_items()) {
      $_SESSION['error'] = 'Cart is empty.';
      redirect('cart');
    }
    $items = cart_items();
    $total = cart_total();
    error_log("Checkout form loaded for user ID " . (current_user()['id'] ?? 'none') . " with " . count($items) . " items");
    view('checkout/form', compact('items', 'total'));
  }

  public function placeOrder() {
    require_auth();
    verify_csrf();
    if (current_user()['role'] !== 'customer') {
        $_SESSION['error'] = 'Only customers can place orders.';
        redirect('dashboard');
    }
    if (!cart_items()) {
        $_SESSION['error'] = 'Cart is empty.';
        redirect('cart');
    }

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    error_log("Place order attempt for user ID " . (current_user()['id'] ?? 'none') . " with name: $name, phone: $phone, address: $address");

    if (!$name || !$phone || !$address) {
        $items = cart_items();
        $total = cart_total();
        $_SESSION['error'] = 'Please fill in all shipping details.';
        error_log("Place order failed: Missing shipping details");
        return view('checkout/form', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    foreach (cart_items() as $item) {
        $product = Product::find($item['id']);
        if (!$product || $product['stock'] < $item['qty']) {
            error_log("Place order failed: Insufficient stock for product ID " . $item['id']);
            $_SESSION['error'] = "Not enough stock for {$item['name']} (available: " . ($product['stock'] ?? 0) . ")";
            return view('checkout/form', [
                'items' => cart_items(),
                'total' => cart_total(),
            ]);
        }
    }

    $pdo = Database::pdo();
    $pdo->beginTransaction();
    try {
        $user_id = current_user()['id'];
        $total = cart_total();
        error_log("Creating order for user ID $user_id with total $total");
        $order_id = Order::create($user_id, $name, $phone, $address, $total, 'pending');

        if (!$order_id) {
            error_log("Order creation failed: Order::create returned false");
            throw new Exception("Failed to create order");
        }

        foreach (cart_items() as $item) {
            error_log("Adding item to order $order_id: ID " . $item['id'] . ", Qty " . $item['qty']);
            Order::addItem($order_id, $item['id'], $item['name'], $item['price'], $item['qty']);

            // NEW: reduce product stock
            $stmt = $pdo->prepare("
                UPDATE products 
                SET stock = stock - :qty 
                WHERE id = :id AND stock >= :qty
            ");
            $ok = $stmt->execute([
                ':qty' => (int)$item['qty'],
                ':id'  => (int)$item['id'],
            ]);
            if (!$ok || $stmt->rowCount() === 0) {
                error_log("Stock update failed for product ID " . $item['id']);
                throw new Exception("Failed to update stock for {$item['name']}");
            }
        }

        $pdo->commit();
        error_log("Order $order_id committed successfully");
        unset($_SESSION['cart']);
        $_SESSION['order_confirmation'] = "Order placed successfully. Shipping address: $address";
        redirect('orders/' . $order_id);
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Order failed: ' . $e->getMessage());
        $_SESSION['error'] = 'Order failed. Please try again: ' . $e->getMessage();
        view('checkout/form', [
            'items' => cart_items(),
            'total' => cart_total(),
        ]);
    }
  }

  public function myOrders() {
    require_auth();
    if (current_user()['role'] !== 'customer') {
      $_SESSION['error'] = 'Only customers can view orders.';
      redirect('dashboard');
    }
    $orders = Order::forUser(current_user()['id']);
    view('orders/index', compact('orders'));
  }

  public function show($id) {
    require_auth();
    if (current_user()['role'] !== 'customer' && !is_admin()) {
      $_SESSION['error'] = 'Access denied.';
      redirect('dashboard');
    }
    $order = Order::findWithItems((int)$id);
    if (!$order) {
      http_response_code(404);
      exit('Order not found');
    }
    if (!is_admin() && $order['user_id'] !== current_user()['id']) {
      http_response_code(403);
      exit('Forbidden');
    }
    view('orders/show', compact('order'));
  }
}
