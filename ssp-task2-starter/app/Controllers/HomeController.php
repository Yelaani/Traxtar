<?php
require_once BASE_PATH . 'Models/Admin.php';
require_once BASE_PATH . 'Models/Customer.php';
require_once BASE_PATH . 'Models/UserHelper.php';
require_once BASE_PATH . 'Models/Product.php'; 
require_once BASE_PATH . 'Views/helpers.php';

class HomeController {
    public function landing() {
        try {
            $products = Product::all(); // Assumes Product model has a static 'all()' method
            view('home', compact('products'));
        } catch (Exception $e) {
            view('home', ['error' => 'Failed to load products: ' . $e->getMessage()]);
        }
    }
    
    public function dashboard() {
        require_auth(); // Ensure user is logged in
        if (is_admin()) {
            header('Location: ' . url('admin/dashboard'));
        } else {
            header('Location: ' . url('customer/dashboard'));
        }
        exit;
    }

    public function adminDashboard() {
        require_auth(); // Ensure user is logged in
        if (!is_admin()) { // Restrict to admins only
            header('Location: ' . url('login'));
            exit;
        }
        view('admin/dashboard', ['user' => $_SESSION['user']]);
    }

    public function customerDashboard() {
        require_auth(); // Ensure user is logged in
        if (is_admin()) { // Restrict to non-admins (customers)
            header('Location: ' . url('login'));
            exit;
        }
        view('customer/dashboard', ['user' => $_SESSION['user']]);
    }
}