<h1 class="text-2xl font-bold mb-6">Register</h1>

<?php if (!empty($error)): ?>
  <div class="alert mb-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= url('register') ?>" class="max-w-md space-y-4">
  <?php csrf_field(); ?>
  <div>
    <label class="label">Name</label>
    <input class="input" type="text" name="name"
           value="<?= htmlspecialchars($name ?? '') ?>" 
           required autocomplete="name">
  </div>
  <div>
    <label class="label">Email</label>
    <input class="input" type="email" name="email"
           value="<?= htmlspecialchars($email ?? '') ?>" 
           required autocomplete="email">
  </div>
  <div>
    <label class="label">Password</label>
    <input class="input" type="password" name="password"
           required autocomplete="new-password">
  </div>
  <div>
    <label class="label">Confirm Password</label>
    <input class="input" type="password" name="password_confirmation"
           required autocomplete="new-password">
  </div>
  <div>
    <label class="label">Role</label>
    <select class="input" name="role" required>
      <option value="customer">Customer</option>
      <option value="admin">Admin (for authorized users only)</option>
    </select>
  </div>
  <button class="btn-primary">Create account</button>
</form>
