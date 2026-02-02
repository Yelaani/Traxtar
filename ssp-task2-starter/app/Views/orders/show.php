<a class="btn mb-4 inline-block" href="<?= url('orders') ?>">← Back to orders</a>

<h1 class="text-2xl font-bold mb-4">Order #<?= (int)$order['id'] ?></h1>

<div class="grid md:grid-cols-2 gap-6">
  <div class="card p-4">
    <h2 class="font-semibold mb-2">Shipping</h2>
    <div class="text-sm">
      <div><?= htmlspecialchars($order['shipping_name']) ?></div>
      <div><?= htmlspecialchars($order['shipping_phone']) ?></div>
      <div class="mt-1 whitespace-pre-line"><?= htmlspecialchars($order['shipping_address']) ?></div>
    </div>
  </div>
  <div class="card p-4">
    <h2 class="font-semibold mb-2">Summary</h2>
    <div class="text-sm flex justify-between"><span>Status</span><span><?= htmlspecialchars($order['status']) ?></span></div>
    <div class="text-sm flex justify-between font-semibold mt-2"><span>Total</span><span>LKR <?= number_format($order['total'],2) ?></span></div>
  </div>
</div>

<div class="card p-4 mt-6">
  <h2 class="font-semibold mb-2">Items</h2>
  <table class="w-full text-sm">
    <thead>
      <tr class="bg-neutral-100">
        <th class="px-3 py-2 text-left">Product</th>
        <th class="px-3 py-2 text-left">Price</th>
        <th class="px-3 py-2 text-left">Qty</th>
        <th class="px-3 py-2 text-left">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($order['items'] as $it): ?>
      <tr class="border-b">
        <td class="px-3 py-2"><?= htmlspecialchars($it['product_name']) ?></td>
        <td class="px-3 py-2">LKR <?= number_format($it['price'],2) ?></td>
        <td class="px-3 py-2"><?= (int)$it['qty'] ?></td>
        <td class="px-3 py-2">LKR <?= number_format($it['price']*$it['qty'],2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
