<div class="max-w-lg card p-6">
  <h2 class="text-xl font-bold mb-4">Create Product</h2>

  <?php if (!empty($error)): ?>
    <div class="alert mb-3"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= url('admin/products') ?>" class="space-y-4">
    <?php csrf_field(); ?>

    <div>
      <label class="label">Name</label>
      <input class="input" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
    </div>

    <div>
      <label class="label">Price (LKR)</label>
      <input class="input" name="price" type="number" step="0.01" min="0"
             value="<?= htmlspecialchars($old['price'] ?? '') ?>" required>
    </div>

    <div>
      <label class="label">Stock</label>
      <input class="input" name="stock" type="number" min="0"
             value="<?= htmlspecialchars($old['stock'] ?? '') ?>" required>
    </div>

    <div>
      <label class="label">Description</label>
      <textarea class="input" name="description" rows="3"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
    </div>

    <div class="flex gap-2">
      <button class="btn-primary">Save</button>
      <a href="<?= url('admin/products') ?>" class="btn">Cancel</a>
    </div>
  </form>
</div>
