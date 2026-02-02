<h1 class="text-2xl font-bold mb-6">Shop</h1>

<?php if (empty($products)): ?>
  <p class="text-neutral-600">No products yet.</p>
<?php else: ?>
  <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($products as $p): ?>
      <li class="card overflow-hidden">
        <div class="bg-neutral-100">
          <?php if (!empty($p['image'])): ?>
            <img src="<?= asset_url($p['image']) ?>" alt="" class="w-full h-48 object-cover">
          <?php else: ?>
            <div class="w-full h-48 flex items-center justify-center text-neutral-400 text-sm">No image</div>
          <?php endif; ?>
        </div>
        <div class="p-4 space-y-2">
          <div class="flex items-start justify-between gap-3">
            <h3 class="font-semibold leading-tight"><?= htmlspecialchars($p['name']) ?></h3>
            <span class="text-sm font-medium whitespace-nowrap">LKR <?= number_format((float)$p['price'], 2) ?></span>
          </div>
          <div class="text-xs text-neutral-600">Stock: <?= (int)$p['stock'] ?></div>
          <a class="btn" href="<?= url('products/' . (int)$p['id']) ?>">View</a>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
