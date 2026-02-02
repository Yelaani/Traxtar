<?php
// ==== Paths & bootstrap ====
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR); // -> app/
}

if (!class_exists('Database')) {
    require_once dirname(__DIR__) . '/Database.php'; // ../Database.php from /app/Views
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==== URL helpers (work for both PHP -S and XAMPP Apache) ====
if (!function_exists('base_url')) {
    function base_url(): string {
        // e.g. "/" for php -S, or "/ssp-task2-starter/public" under XAMPP
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $base = rtrim($dir, '/');
        return $base === '' ? '' : $base;
    }
}
if (!function_exists('url')) {
    function url(string $path = ''): string {
        return base_url() . '/' . ltrim($path, '/');
    }
}
if (!function_exists('asset_url')) {
    function asset_url(string $path): string {
        return url($path);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302) {
        header('Location: ' . url(ltrim($path, '/')), true, $status);
        exit;
    }
}


// ==== Auth helpers ====
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool { return !empty($_SESSION['user']); }
}
if (!function_exists('current_user')) {
    function current_user() { return $_SESSION['user'] ?? null; }
}
if (!function_exists('is_guest')) {
    function is_guest(): bool { return empty($_SESSION['user']); }
}
if (!function_exists('user_role')) {
    function user_role(): ?string { return $_SESSION['user']['role'] ?? null; }
}
if (!function_exists('is_admin')) {
    function is_admin(): bool { return user_role() === 'admin'; }
}
if (!function_exists('is_customer')) {
    function is_customer(): bool { return user_role() === 'customer'; }
}
if (!function_exists('require_auth')) {
    function require_auth($role = null) {
        if (is_guest()) { header('Location: ' . url('login')); exit; }
        if ($role && user_role() !== $role) { http_response_code(403); exit('Forbidden'); }
    }
}

// ==== CSRF helpers ====
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
if (!function_exists('csrf_field')) {
    function csrf_field() {
        $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
        echo '<input type="hidden" name="csrf_token" value="'.$t.'">';
    }
}
if (!function_exists('verify_csrf')) {
    function verify_csrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
                http_response_code(419);
                exit('Invalid CSRF token');
            }
        }
    }
}


if (!function_exists('view')) {
    function view($name, $data = []) {
        extract($data, EXTR_SKIP);

        $file = __DIR__ . '/' . ltrim($name, '/\\') . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            include __DIR__ . '/partials/header.php';
            echo '<div class="alert">View not found: <code>' . htmlspecialchars($file) . '</code></div>';
            include __DIR__ . '/partials/footer.php';
            exit;
        }

        include __DIR__ . '/partials/header.php';
        include $file;
        include __DIR__ . '/partials/footer.php';
    }
}

// ---- Cart helpers (session-based) ----
function cart_items(): array { return $_SESSION['cart'] ?? []; }
function cart_count(): int {
  $n = 0; foreach (cart_items() as $it) { $n += (int)$it['qty']; } return $n;
}
function cart_total(): float {
  $sum = 0; foreach (cart_items() as $it) { $sum += (float)$it['price'] * (int)$it['qty']; } return $sum;
}
