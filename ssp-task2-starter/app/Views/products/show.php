<a class="btn mb-4 inline-block" href="<?= url('products') ?>">← Back to shop</a>

<div class="grid md:grid-cols-2 gap-6">
  <div>
    <?php if (!empty($product['image'])): ?>
      <img src="<?= asset_url($product['image']) ?>" alt="" class="w-full rounded">
    <?php else: ?>
      <div class="w-full h-64 bg-neutral-200 rounded"></div>
    <?php endif; ?>
  </div>
  <div>
    <h1 class="text-2xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h1>
    <p class="text-neutral-600 mb-4"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>
    <div class="text-xl font-semibold mb-4">LKR <?= number_format((float)$product['price'], 2) ?></div>
    <div class="text-sm text-neutral-600 mb-6">Stock: <?= (int)$product['stock'] ?></div>
    <form method="POST" action="<?= url('cart/add') ?>" class="flex items-center gap-2">
        <?php csrf_field(); ?>
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <input class="input w-24" type="number" name="qty" value="1" min="1">
        <button class="btn-primary" type="submit">Add to Cart</button>
    </form>

</div>
</div>
