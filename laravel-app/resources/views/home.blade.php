@extends('layouts.traxtar')

@section('content')
<!-- Full-screen, full-bleed hero -->
<div class="relative w-screen h-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw]">
  <!-- Background image -->
  <img src="{{ asset('images/hero.jpg') }}" alt="Hero"
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
    <a href="{{ route('products.shop') }}" class="mt-6 btn-primary px-6 py-3 text-lg">
      Shop Now
    </a>
  </div>
</div>

<!-- Products Grid Section -->
<section class="py-12 max-w-6xl mx-auto">
  <h2 class="text-3xl font-bold mb-6">Featured Products</h2>

  <div class="grid md:grid-cols-3 gap-6">
    @forelse($products as $product)
      <div class="card border rounded-lg overflow-hidden shadow hover:shadow-lg transition">
        <div class="bg-neutral-100">
          @if($product->image)
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
          @else
            <div class="w-full h-48 flex items-center justify-center text-neutral-400 text-sm">No image</div>
          @endif
        </div>
        <div class="p-4">
          <h3 class="font-semibold text-lg">
            {{ $product->name }}
          </h3>
          <p class="text-sm text-neutral-600 mb-2 line-clamp-2">
            {{ $product->description }}
          </p>
          <div class="flex items-center justify-between">
            <span class="font-bold">
              {{ $product->formatted_price }}
            </span>
            <span class="text-xs text-neutral-500">
              Stock: {{ $product->stock }}
            </span>
          </div>
          <a href="{{ route('products.show', $product) }}" class="mt-2 inline-block text-blue-600 hover:underline">
            View Details
          </a>
        </div>
      </div>
    @empty
      <p class="text-neutral-600">No products available.</p>
    @endforelse
  </div>
</section>
@endsection
