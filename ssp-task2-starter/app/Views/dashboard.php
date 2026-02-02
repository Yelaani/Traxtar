<?php $u=current_user(); ?>
<h2 class="text-2xl font-bold mb-4">Dashboard</h2>
<div class="grid md:grid-cols-2 gap-4">
  <div class="card p-4">
    <p class="mb-2">Hello, <span class="font-semibold"><?php echo htmlspecialchars($u['name']); ?></span>! You are logged in as <span class="badge"><?php echo htmlspecialchars($u['role']); ?></span>.</p>
    <ul class="list-disc ml-6 text-sm text-neutral-700">
      <?php if ($u['role']==='admin'): ?>
        <li>Manage products (Create/Read/Update/Delete)</li>
        <li>View all user accounts (coming soon)</li>
      <?php else: ?>
        <li>Browse products on the landing page</li>
        <li>Future: place orders, view your history</li>
      <?php endif; ?>
    </ul>
  </div>
  <div class="card p-4">
    <h3 class="font-semibold">Quick Links</h3>
    <div class="mt-2 space-x-2">
      <?php if ($u['role']==='admin'): ?>
        <a href="/admin/products" class="btn-primary">Manage Products</a>
      <?php else: ?>
        <a href="/" class="btn-primary">Shop Now</a>
      <?php endif; ?>
    </div>
  </div>
</div>
