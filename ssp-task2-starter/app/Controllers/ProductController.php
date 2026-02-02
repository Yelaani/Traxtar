<?php
// Ensure helpers + model are loaded
require_once __DIR__ . '/../Views/helpers.php';
require_once BASE_PATH . 'Models/Product.php';

class ProductController {

    /* ===== PUBLIC PAGES ===== */

    // Public product list (Shop)
    public function shop() {
        $products = Product::all();           
        view('products/shop', compact('products'));
    }

    // Public product detail
    public function show($id) {
        $product = Product::find($id);
        if (!$product) { http_response_code(404); exit('Product not found'); }
        view('products/show', compact('product'));
    }

    /* ===== ADMIN PAGES ===== */

    // Admin list
    public function index() {
        require_auth('admin');
        $products = Product::all();
        view('products/index', compact('products'));
    }

    // Admin create form
    public function createForm() {
        require_auth('admin');
        view('products/create');
    }

    // Admin store
    public function store() {
        require_auth('admin');
        verify_csrf();

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'sku'         => trim($_POST['sku'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => (float)($_POST['price'] ?? 0),
            'stock'       => (int)($_POST['stock'] ?? 0),
            'category_id' => ($_POST['category_id'] ?? '') === '' ? null : (int)$_POST['category_id'],
            'image'       => null,
        ];

        if ($data['name'] === '' || $data['price'] <= 0) {
            return view('products/create', ['error' => 'Name and a positive price are required.', 'old' => $_POST]);
        }

        // uploads -> projectRoot/public/uploads/
        $uploads = dirname(__DIR__, 2) . '/public/uploads/';
        if (!is_dir($uploads)) { mkdir($uploads, 0777, true); }

        if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed, true)) {
                $filename = uniqid('p_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploads . $filename)) {
                    $data['image'] = 'uploads/' . $filename; // relative to /public
                }
            }
        }

        Product::create($data);
        header('Location: ' . url('admin/products')); exit;
    }

    // Admin edit form
    public function editForm($id) {
        require_auth('admin');
        $product = Product::find($id);
        if (!$product) { http_response_code(404); exit('Product not found'); }
        view('products/edit', compact('product'));
    }

    // Admin update
    public function update($id) {
        require_auth('admin');
        verify_csrf();

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'sku'         => trim($_POST['sku'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => (float)($_POST['price'] ?? 0),
            'stock'       => (int)($_POST['stock'] ?? 0),
            'category_id' => ($_POST['category_id'] ?? '') === '' ? null : (int)$_POST['category_id'],
            // image handled below
        ];

        // uploads dir
        $uploads = dirname(__DIR__, 2) . '/public/uploads/';
        if (!is_dir($uploads)) { mkdir($uploads, 0777, true); }

        if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed, true)) {
                $filename = uniqid('p_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploads . $filename)) {
                    $data['image'] = 'uploads/' . $filename;
                }
            }
        }

        Product::update($id, $data);
        header('Location: ' . url('admin/products')); exit;
    }

    // Admin delete
    public function delete($id) {
        require_auth('admin');
        verify_csrf();
        Product::delete($id);
        header('Location: ' . url('admin/products')); exit;
    }
}
