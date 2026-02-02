<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// also show fatal errors that would normally be blank:
register_shutdown_function(function () {
  $e = error_get_last();
  if ($e && in_array($e['type'], [E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR])) {
    http_response_code(500);
    echo "<pre style='white-space:pre-wrap;background:#111;color:#fff;padding:12px'>FATAL: {$e['message']} in {$e['file']}:{$e['line']}</pre>";
  }
});

if (!defined('BASE_PATH')) {
  define('BASE_PATH', dirname(__DIR__)); // C:\xampp\htdocs\CB015938_year 2_sem 1_SSP task 2
}

require_once BASE_PATH . '/app/bootstrap.php';
require_once BASE_PATH . '/app/Controllers/AuthController.php';
require_once BASE_PATH . '/app/Controllers/ProductController.php';
require_once BASE_PATH . '/app/Controllers/HomeController.php';
require_once BASE_PATH . '/app/Controllers/CartController.php';
require_once BASE_PATH . '/app/Controllers/OrderController.php';


$auth = new AuthController();
$prod = new ProductController();
$home = new HomeController();
$cart = new CartController();
$order = new OrderController();

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base   = rtrim(dirname($script), '/\\');  // e.g. /ssp-task2-starter/public
$path   = $uri;

if ($base !== '' && strpos($uri, $base) === 0) {
    $path = substr($uri, strlen($base));
}
if ($path === false || $path === '') $path = '/';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ---------------- ROUTER ---------------- //
switch (true) {
    /* ========= PUBLIC ========= */
    case $path === '/' && $method === 'GET':
        $home->landing();
        break;

    case $path === '/products' && $method === 'GET':
        $prod->shop();
        break;

    case preg_match('#^/products/(\d+)$#', $path, $m) && $method === 'GET':
        $prod->show((int)$m[1]);
        break;

    /* ========= AUTH ========= */
    case $path === '/login' && $method === 'GET':     $auth->showLogin();    break;
    case $path === '/login' && $method === 'POST':    $auth->login();        break;
    case $path === '/register' && $method === 'GET':  $auth->showRegister(); break;
    case $path === '/register' && $method === 'POST': $auth->register();     break;
    case $path === '/logout' && $method === 'POST':   $auth->logout();       break;

    /* ========= DASHBOARD ========= */
    case $path === '/dashboard' && $method === 'GET':
        $home->dashboard();
        break;

    /* ========= ADMIN PRODUCTS (CRUD) ========= */
    case $path === '/admin/products' && $method === 'GET':
        $prod->index(); break;

    case $path === '/admin/products/create' && $method === 'GET':
        $prod->createForm(); break;

    case $path === '/admin/products' && $method === 'POST':
        $prod->store(); break;

    case preg_match('#^/admin/products/(\d+)/edit$#', $path, $m) && $method === 'GET':
        $prod->editForm((int)$m[1]); break;

    case preg_match('#^/admin/products/(\d+)$#', $path, $m) && $method === 'POST' && (($_POST['_method'] ?? '') === 'PUT'):
        $prod->update((int)$m[1]); break;

    case preg_match('#^/admin/products/(\d+)$#', $path, $m) && $method === 'POST' && (($_POST['_method'] ?? '') === 'DELETE'):
        $prod->delete((int)$m[1]); break;

    /* ========= CUSTOMER CART + ORDERS ========= */
    // Cart page
    case $path === '/cart' && $method === 'GET':
        $cart->index();  // Show the cart
        break;

    // Add to cart
    case $path === '/cart/add' && $method === 'POST':
        $cart->add();  // Add item to the cart
        break;

    // Update cart (change item quantity)
    case $path === '/cart/update' && $method === 'POST':
        $cart->update();  // Update cart quantities
        break;

    // Remove item from cart
    case $path === '/cart/remove' && $method === 'POST':
        $cart->remove();  // Remove item from cart
        break;

    // Clear cart
    case $path === '/cart/clear' && $method === 'POST':
        $cart->clear();  // Clear all items in the cart
        break;

    // Checkout page (Shipping details)
    case $path === '/checkout' && $method === 'GET':
        $order->checkoutForm();  // Show checkout form
        break;

    // Place order (after the user submits shipping details)
    case $path === '/checkout/complete' && $method === 'POST':
        $order->placeOrder();  // Place the order
        break;

    // Show orders (user orders list)
    case $path === '/orders' && $method === 'GET':
        $order->myOrders();  // Show user's orders
        break;

    // Show specific order details
    case preg_match('#^/orders/(\d+)$#', $path, $m) && $method === 'GET':
        $order->show((int)$m[1]);  // Show details for a specific order
        break;

    case $path === '/admin/dashboard' && $method === 'GET':
        $home->adminDashboard();
        break;

    case $path === '/customer/dashboard' && $method === 'GET':
        $home->customerDashboard();
        break;
}