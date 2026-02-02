<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold">All Products</h1>
  <a href="<?= url('admin/products/create') ?>" class="btn-primary">New Product</a>
</div>

<?php if (empty($products)): ?>
  <p class="text-neutral-600">No products yet. <a class="underline" href="<?= url('admin/products/create') ?>">Create one</a>.</p>
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
          <div class="text-xs">
            <?php if ((int)$p['stock'] < 5): ?>
              <span class="px-2 py-1 text-red-800 bg-red-100 rounded-full font-medium">
                Low: <?= (int)$p['stock'] ?>
              </span>
            <?php else: ?>
              <span class="px-2 py-1 text-green-800 bg-green-100 rounded-full font-medium">
                <?= (int)$p['stock'] ?>
              </span>
            <?php endif; ?>
          </div>
          <div class="flex gap-2 pt-2">
            <a class="btn" href="<?= url('admin/products/' . (int)$p['id'] . '/edit') ?>">Edit</a>
            <form method="POST" action="<?= url('admin/products/' . (int)$p['id']) ?>"
                  onsubmit="return confirm('Delete this product?')">
              <?php csrf_field(); ?>
              <input type="hidden" name="_method" value="DELETE">
              <button class="btn-danger" type="submit">Delete</button>
            </form>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
