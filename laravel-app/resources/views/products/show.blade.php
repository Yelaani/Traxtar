@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('products.shop') }}">← Back to shop</a>

<div class="grid md:grid-cols-2 gap-6">
  <div>
    @if($product->image)
      <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full rounded">
    @else
      <div class="w-full h-64 bg-neutral-200 rounded"></div>
    @endif
  </div>
  <div>
    <h1 class="text-2xl font-bold mb-2">{{ $product->name }}</h1>
    <p class="text-neutral-600 mb-4">{!! nl2br(e($product->description ?? '')) !!}</p>
    <div class="text-xl font-semibold mb-4">{{ $product->formatted_price }}</div>
    <div class="text-sm text-neutral-600 mb-6">Stock: {{ $product->stock }}</div>
    
    @if((!auth()->check() || !auth()->user()->isAdmin()) && $product->isInStock())
      <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-2">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input class="input w-24" type="number" name="qty" value="1" min="1" max="{{ $product->stock }}">
        <button class="btn-primary" type="submit">Add to Cart</button>
      </form>
    @elseif(!$product->isInStock())
      <p class="text-red-600 font-semibold">Out of Stock</p>
    @endif
  </div>
</div>
@endsection
