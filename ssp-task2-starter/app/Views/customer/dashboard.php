<h1 class="text-3xl font-heading font-bold mb-6">
  Welcome, <?= htmlspecialchars(current_user()['name'] ?? 'Customer') ?>
</h1>

<!-- Stats Cards -->
<div class="grid gap-6 md:grid-cols-3 mt-6">
  <!-- Role -->
  <div class="card p-6 text-center hover:shadow-md transition">
    <div class="text-brand-600 mb-2">
      <!-- User Icon -->
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1112 21v-2a7 7 0 00-6.879-7.196z" />
      </svg>
    </div>
    <div class="text-sm text-neutral-600">Your Role</div>
    <div class="text-xl font-heading font-bold mt-1">Customer</div>
  </div>

  
    
  </div>

  <!-- Wishlist -->
  <div class="card p-6 text-center hover:shadow-md transition">
    <div class="text-brand-600 mb-2">
      <!-- Heart Icon -->
      
</div>

<!-- Recent Orders -->
<div class="card p-6 mt-8">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-heading text-lg font-bold">Recent Orders</h2>
    <a class="btn-primary" href="<?= url('products') ?>">Shop Now</a>
  </div>

  <div class="text-neutral-600 text-sm">
    <p>You have no orders yet.</p>
  </div>
</div>
