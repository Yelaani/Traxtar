<!-- Full-screen, full-bleed hero -->
<div class="relative w-screen h-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw]">
  <!-- Background image -->
  <img src="<?= asset_url('images/hero.jpg') ?>" alt="Hero"
       class="absolute inset-0 w-full h-full object-cover">

  <!-- Dark overlay for contrast -->
  <div class="absolute inset-0 bg-black/60"></div>

  <!-- Centered white copy -->
  <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-4 text-white">
    <h1 class="text-4xl md:text-6xl font-heading font-bold">
      Traxtar — Move Faster, Look Better
    </h1>
    <p class="mt-4 text-lg md:text-xl font-inter max-w-2xl">
      Quality sportswear for athletes and gym lovers.
    </p>
    <a href="<?= url('products') ?>" class="mt-6 btn-primary px-6 py-3 text-lg">
      Shop Now
    </a>
  </div>
</div>


<!-- Products Grid Section -->
<section class="py-12 max-w-6xl mx-auto">
  <h2 class="text-3xl font-bold mb-4">Featured Products</h2>
  <p class="text-neutral-600 mb-6">
    A tiny ecommerce demo built with PHP, MySQL & Tailwind.
  </p>

  <div class="grid md:grid-cols-3 gap-6">
    <?php foreach ($products as $p): ?>
      <div class="card border rounded-lg overflow-hidden shadow hover:shadow-lg transition">
        <div class="p-4">
          <h3 class="font-semibold text-lg">
            <?= htmlspecialchars($p['name']); ?>
          </h3>
          <p class="text-sm text-neutral-600 mb-2 line-clamp-2">
            <?= htmlspecialchars($p['description']); ?>
          </p>
          <div class="flex items-center justify-between">
            <span class="font-bold">
              LKR <?= number_format($p['price'], 2); ?>
            </span>
            <span class="text-xs text-neutral-500">
              Stock: <?= (int)$p['stock']; ?>
            </span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
