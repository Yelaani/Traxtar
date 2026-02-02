<div class="max-w-lg card p-6">
  <h2 class="text-xl font-bold mb-4">Edit Product</h2>

  <form method="POST" action="<?= url('admin/products/' . (int)$product['id']) ?>" class="space-y-3">
    <?php csrf_field(); ?>
    <input type="hidden" name="_method" value="PUT">

    <div>
      <label class="label">Name</label>
      <input class="input" name="name" required value="<?= htmlspecialchars($product['name']) ?>">
    </div>

    <div>
      <label class="label">Price (LKR)</label>
      <input class="input" name="price" type="number" step="0.01" min="0" required
             value="<?= htmlspecialchars($product['price']) ?>">
    </div>

    <div>
      <label class="label">Stock</label>
      <input class="input" name="stock" type="number" min="0" required
             value="<?= htmlspecialchars($product['stock']) ?>">
    </div>

    <div>
      <label class="label">Description</label>
      <textarea class="input" name="description" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
    </div>

    <div class="flex gap-2">
      <button class="btn-primary">Update</button>
      <a class="btn" href="<?= url('admin/products') ?>">Cancel</a>
    </div>
  </form>
</div>
