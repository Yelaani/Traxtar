<h1 class="text-2xl font-bold mb-6">Login</h1>

<?php if (!empty($error)): ?>
  <div class="alert mb-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= url('login') ?>" class="max-w-md space-y-4">
  <?php csrf_field(); ?>
  <div>
    <label class="label">Email</label>
    <input class="input" type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
  </div>
  <div>
    <label class="label">Password</label>
    <input class="input" type="password" name="password" required>
  </div>
  <button class="btn-primary">Sign in</button>
</form>
