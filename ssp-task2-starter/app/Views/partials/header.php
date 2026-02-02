<?php
if (!function_exists('nav_link')) {
    function nav_link(string $path, string $label) {
        $href = url($path);
        $req  = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $dest = rtrim(parse_url($href, PHP_URL_PATH), '/');
        $active = $req === $dest;
        $cls = 'hover:underline';
        if ($active) $cls .= ' bg-white/20 rounded px-2 py-1';
        echo '<a href="'.$href.'" class="'.$cls.'">'.$label.'</a>';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TRAXTAR</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset_url('css/tailwind.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('css/custom.css') ?>"><!-- optional -->
</head>
<body class="font-inter bg-neutral-50 min-h-screen">

  <nav class="bg-brand-600 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <a class="font-bold" href="<?= url('') ?>">TRAXTAR</a>

      <div class="space-x-4">
        <?php $u = is_logged_in() ? current_user() : null; ?>

        <?php nav_link('', 'Home'); ?>

        <?php if (($u['role'] ?? null) === 'customer'): ?>
          <?php nav_link('products', 'Shop'); ?>
        <?php endif; ?>

        <!-- Cart link with live count badge -->
        <a href="<?= url('cart') ?>" class="hover:underline">
          Cart<?= (function_exists('cart_count') && cart_count()) ? ' ('.cart_count().')' : '' ?>
        </a>

        <?php if (!is_logged_in()): ?>

          <?php nav_link('login', 'Login'); ?>
          <?php nav_link('register', 'Register'); ?>

        <?php else: ?>

          <?php nav_link('dashboard', 'Dashboard'); ?>

          <?php if (($u['role'] ?? null) === 'admin'): ?>
            <?php nav_link('admin/products', 'Products'); ?>
          <?php else: ?>
            <?php nav_link('orders', 'My Orders'); ?>
          <?php endif; ?>

          <form action="<?= url('logout') ?>" method="POST" class="inline">
            <?php csrf_field(); ?>
            <button type="submit" class="btn">Logout</button>
          </form>

        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto p-4">
