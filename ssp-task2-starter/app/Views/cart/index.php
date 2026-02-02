<h1 class="text-2xl font-bold mb-6">Your Cart</h1>

<?php if (!empty($_SESSION['cart_message'])): ?>
  <div class="bg-green-500 text-white p-4 rounded mb-4">
    <?= htmlspecialchars($_SESSION['cart_message']) ?>
  </div>
  <?php unset($_SESSION['cart_message']); ?>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="bg-red-500 text-white p-4 rounded mb-4">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<?php if (empty($items)): ?>
  <p class="text-neutral-600">Your cart is empty.</p>
  <a class="btn mt-4 inline-block" href="<?= url('products') ?>">Continue shopping</a>
<?php else: ?>
  <form method="POST" action="<?= url('cart/update') ?>" class="space-y-4">
    <?php csrf_field(); ?>
    <ul class="space-y-3">
      <?php foreach ($items as $it): ?>
        <li class="card p-4 flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <?php if (!empty($it['image'])): ?>
              <img src="<?= asset_url($it['image']) ?>" class="w-16 h-16 rounded object-cover">
            <?php else: ?>
              <div class="w-16 h-16 bg-neutral-200 rounded"></div>
            <?php endif; ?>
            <div>
              <div class="font-semibold"><?= htmlspecialchars($it['name']) ?></div>
              <div class="text-sm text-neutral-600">LKR <?= number_format($it['price'], 2) ?></div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <input type="number" name="qty[<?= (int)$it['id'] ?>]" value="<?= (int)$it['qty'] ?>" min="0" class="input w-20">
            <button type="submit" name="remove" value="<?= (int)$it['id'] ?>" class="btn-danger">Remove</button>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="flex items-center justify-between pt-2">
      <div class="text-lg font-semibold">Total: LKR <?= number_format($total, 2) ?></div>
      <div class="flex gap-2">
        <button type="submit" name="update" class="btn">Update</button>
        <button type="submit" name="clear" class="btn">Clear</button>
        <a class="btn-primary" href="<?= url('checkout') ?>">Checkout</a>
      </div>
    </div>
  </form>
<?php endif; ?>