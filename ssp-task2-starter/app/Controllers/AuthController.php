<?php

// Controllers assume app/bootstrap.php was already required by public/index.php
require_once BASE_PATH . 'Models/Admin.php';
require_once BASE_PATH . 'Models/Customer.php';
require_once BASE_PATH . 'Models/UserHelper.php';


class AuthController
{
    public function showLogin() { view('login'); }
    public function showRegister() { view('register'); }

    public function login() {
        verify_csrf();

        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass = $_POST['password'] ?? '';

        $user = Admin::findByEmail($email);
        $role = 'admin';
        if (!$user) { $user = Customer::findByEmail($email); $role = 'customer'; }

        if (!$user) {
            return view('login', ['error' => 'Invalid credentials', 'email' => $email]);
        }

        $stored = $user['password_hash'] ?? $user['password'] ?? '';

        $ok = false;

        if ($stored !== '') {
            $ok = str_starts_with($stored, '$')
                ? password_verify($pass, $stored)
                : hash_equals($stored, $pass);
        }

        if (!$ok) {
            return view('login', ['error' => 'Invalid credentials', 'email' => $email]);
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'] ?? $user['username'] ?? '',
            'email' => $user['email'],
            'role' => $role,
        ];
        session_regenerate_id(true);

        if ($stored !== '' && !str_starts_with($stored, '$')) {
            $new = password_hash($pass, PASSWORD_DEFAULT);
            if ($role === 'admin') Admin::updatePassword((int)$user['id'], $new);
            else Customer::updatePassword((int)$user['id'], $new);
        }

        $redirect = ($role === 'admin') ? '/admin/dashboard' : '/customer/dashboard';
        header('Location: ' . url($redirect));
        exit;
    }

    public function register() {
        verify_csrf();

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $role = ($_POST['role'] ?? 'customer') === 'admin' ? 'admin' : 'customer';
        error_log("Registering role: " . $role); // Debug log
        if ($name === '' || $email === '' || strlen($password) < 6 || $password !== $confirm) {
            return view('register', ['error' => 'Please fill all fields (password min 6, both match).',
                                    'name' => $name, 'email' => $email]);
        }

        if (Admin::findByEmail($email) || Customer::findByEmail($email)) {
            return view('register', ['error' => 'Email already in use.', 'name' => $name, 'email' => $email]);
        }

        $hash = UserHelper::hashPassword($password);

        if ($role === 'admin') {
            $success = Admin::create([
                'name' => $name,
                'email' => $email,
                'password' => $hash,
            ]);
            if (!$success) {
                return view('register', ['error' => 'Failed to create admin account.', 'name' => $name, 'email' => $email]);
            }
        } else {
            $success = Customer::create([
                'name' => $name,
                'email' => $email,
                'password' => $hash,
                'phone' => $phone,
                'address' => $address,
            ]);
            if (!$success) {
                return view('register', ['error' => 'Failed to create customer account.', 'name' => $name, 'email' => $email]);
            }
        }

        $this->redirect('login');
    }

    public function logout() {
        verify_csrf();
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->redirect('');
    }

    private function redirect(string $path) {
        header('Location: ' . url($path));
        exit;
    }
}