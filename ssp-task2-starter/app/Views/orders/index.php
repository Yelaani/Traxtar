<h1 class="text-2xl font-bold mb-6">My Orders</h1>

<?php if (empty($orders)): ?>
  <p class="text-neutral-600">No orders yet.</p>
  <a class="btn mt-4 inline-block" href="<?= url('products') ?>">Shop now</a>
<?php else: ?>
  <table class="w-full text-sm">
    <thead>
      <tr class="bg-neutral-100">
        <th class="px-3 py-2 text-left">Order #</th>
        <th class="px-3 py-2 text-left">Date</th>
        <th class="px-3 py-2 text-left">Total</th>
        <th class="px-3 py-2 text-left">Status</th>
        <th class="px-3 py-2"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
      <tr class="border-b">
        <td class="px-3 py-2"><?= (int)$o['id'] ?></td>
        <td class="px-3 py-2"><?= htmlspecialchars($o['created_at']) ?></td>
        <td class="px-3 py-2">LKR <?= number_format($o['total'], 2) ?></td>
        <td class="px-3 py-2"><?= htmlspecialchars($o['status']) ?></td>
        <td class="px-3 py-2 text-right">
          <a class="btn" href="<?= url('orders/' . (int)$o['id']) ?>">View</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
