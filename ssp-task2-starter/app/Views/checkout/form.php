<h1 class="text-2xl font-bold mb-6">Checkout</h1>

<?php if (!empty($_SESSION['order_confirmation'])): ?>
  <div id="notification" class="bg-green-500 text-white p-4 rounded mb-4">
    <?= htmlspecialchars($_SESSION['order_confirmation']) ?>
  </div>
  <?php unset($_SESSION['order_confirmation']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
  <div id="notification" class="bg-red-500 text-white p-4 rounded mb-4">
    <?= htmlspecialchars($_SESSION['error']) ?>
  </div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="POST" action="<?= url('checkout/complete') ?>" class="space-y-4">
  <?php csrf_field(); ?>
  <div>
    <label class="label">Name</label>
    <input class="input" type="text" name="name" placeholder="Enter your name" required>
  </div>
  <div>
    <label class="label">Phone</label>
    <input class="input" type="text" name="phone" placeholder="Enter your phone number" required>
  </div>
  <div>
    <label class="label">Address</label>
    <textarea class="input" name="address" rows="4" placeholder="Enter your shipping address" required></textarea>
  </div>
  <button class="btn-primary">Place Order</button>
</form>

<div class="card p-4 mt-6">
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