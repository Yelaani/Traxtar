<h1 class="text-2xl font-bold mb-6">Checkout</h1>

<?php if (!empty($error)): ?>
  <div class="alert mb-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="grid md:grid-cols-2 gap-6">
  <form method="POST" action="<?= url('checkout') ?>" class="card p-4 space-y-3">
    <?php csrf_field(); ?>
    <h2 class="font-semibold mb-2">Shipping Details</h2>
    <div>
      <label class="label">Name</label>
      <input class="input" name="name" required>
    </div>
    <div>
      <label class="label">Phone</label>
      <input class="input" name="phone" required>
    </div>
    <div>
      <label class="label">Address</label>
      <textarea class="input" name="address" rows="3" required></textarea>
    </div>
    <button class="btn-primary">Place Order</button>
  </form>

  <div class="card p-4">
    <h2 class="font-semibold mb-2">Order Summary</h2>
    <ul class="space-y-2">
      <?php foreach ($items as $it): ?>
        <li class="flex justify-between text-sm">
          <span><?= htmlspecialchars($it['name']) ?> × <?= (int)$it['qty'] ?></span>
          <span>LKR <?= number_format($it['price'] * $it['qty'], 2) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="border-t mt-3 pt-3 flex justify-between font-semibold">
      <span>Total</span>
      <span>LKR <?= number_format($total, 2) ?></span>
    </div>
  </div>
</div>
