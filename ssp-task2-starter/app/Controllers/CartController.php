<?php
require_once __DIR__ . '/../Views/helpers.php';
require_once BASE_PATH . 'Models/Product.php';
require_once BASE_PATH . 'Models/Order.php';

class CartController {
    private function &cartRef() {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        return $_SESSION['cart'];
    }

    // Show cart items
    public function index() {
        $items = cart_items(); // Cart items
        $total = cart_total(); // Total price calculation
        view('cart/index', compact('items', 'total'));
    }

    // Add to cart
    public function add() {
        verify_csrf();
        $id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));

        $p = Product::find($id);
        if (!$p) {
            $_SESSION['error'] = 'Product not found.';
            redirect('products');
        }

        // Check stock
        if ($p['stock'] < $qty) {
            $_SESSION['error'] = "Insufficient stock for {$p['name']} (available: {$p['stock']}).";
            redirect('products');
        }

        $cart =& $this->cartRef();
        if (!isset($cart[$id])) {
            $cart[$id] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'price' => (float)$p['price'],
                'qty' => 0,
                'image' => $p['image'] ?? null,
            ];
        }
        $cart[$id]['qty'] += $qty;

        $_SESSION['cart_message'] = 'Item added to cart.';
        redirect('cart');
    }

    // Update cart (handles update, remove, and clear actions)
    public function update() {
        verify_csrf();
        require_auth(); // Ensure user is logged in
        if (current_user()['role'] !== 'customer') {
            $_SESSION['error'] = 'Only customers can modify the cart.';
            redirect('dashboard');
        }

        $cart =& $this->cartRef();
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
            $productId = (int)$_POST['remove'];
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                $_SESSION['cart_message'] = 'Item removed.';
            } else {
                $_SESSION['error'] = 'Item not found in cart.';
            }
            redirect('cart');
        }

        // Handle update quantities
        if (isset($_POST['update']) && isset($_POST['qty']) && is_array($_POST['qty'])) {
            $error = null;
            foreach ($_POST['qty'] as $productId => $qty) {
                $productId = (int)$productId;
                $qty = (int)$qty;

                if ($qty <= 0) {
                    unset($cart[$productId]); // Remove if quantity is 0 or less
                    continue;
                }

                if (!isset($cart[$productId])) {
                    $error = "Product ID $productId not found in cart.";
                    break;
                }

                $product = Product::find($productId);
                if (!$product) {
                    $error = "Product {$cart[$productId]['name']} not found.";
                    break;
                }
                if ($product['stock'] < $qty) {
                    $error = "Insufficient stock for {$cart[$productId]['name']} (available: {$product['stock']}).";
                    break;
                }

                $cart[$productId]['qty'] = $qty; // Update quantity
            }

            if ($error) {
                $items = cart_items();
                $total = cart_total();
                view('cart/index', compact('items', 'total', 'error'));
            } else {
                $_SESSION['cart_message'] = 'Cart updated.';
                redirect('cart');
            }
        }

        redirect('cart');
    }

    // Checkout method
    public function checkout() {
        require_auth();
        if (current_user()['role'] !== 'customer') {
            $_SESSION['error'] = 'Only customers can place orders.';
            redirect('dashboard');
        }
        $items = cart_items();
        $total = cart_total();
        if (empty($items)) {
            $_SESSION['error'] = 'Cart is empty.';
            redirect('cart');
        }
        view('checkout/form', compact('items', 'total'));
    }

    // Complete order
    public function complete() {
        verify_csrf();
        require_auth();
        if (current_user()['role'] !== 'customer') {
            $_SESSION['error'] = 'Only customers can place orders.';
            redirect('dashboard');
        }

        $items = cart_items();
        if (empty($items)) {
            $_SESSION['error'] = 'Cart is empty.';
            redirect('cart');
        }

        $address = trim($_POST['address'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!$name || !$phone || !$address) {
            $total = cart_total();
            return view('checkout/form', [
                'items' => $items,
                'total' => $total,
                'error' => 'Please fill in all shipping details.'
            ]);
        }

        foreach ($items as $item) {
            $product = Product::find($item['id']);
            if (!$product || $product['stock'] < $item['qty']) {
                return view('checkout/form', [
                    'items' => $items,
                    'total' => cart_total(),
                    'error' => "Not enough stock for {$item['name']} (available: " . ($product['stock'] ?? 0) . ")"
                ]);
            }
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $user_id = current_user()['id'];
            $total = cart_total();
            $order_id = Order::create($user_id, $name, $phone, $address, $total, 'pending');

            foreach ($items as $item) {
                Order::addItem($order_id, $item['id'], $item['name'], $item['price'], $item['qty']);
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                $stmt->execute([(int)$item['qty'], (int)$item['id'], (int)$item['qty']]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            $_SESSION['order_confirmation'] = "Order #$order_id has been placed. Shipping address: $address";
            redirect('orders/' . $order_id);
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('Order failed: ' . $e->getMessage());
            view('checkout/form', [
                'items' => $items,
                'total' => cart_total(),
                'error' => 'Order failed. Please try again.'
            ]);
        }
    }
}