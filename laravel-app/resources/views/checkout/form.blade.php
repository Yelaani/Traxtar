@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('cart.index') }}">← Back to cart</a>

<h1 class="text-2xl font-bold mb-6">Checkout</h1>

<form method="POST" action="{{ route('orders.place') }}" class="space-y-4">
  @csrf
  <div>
    <label class="label">Name</label>
    <input class="input" type="text" name="shipping_name" value="{{ old('shipping_name', auth()->user()->name) }}" placeholder="Enter your name" required>
    @error('shipping_name')
      <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
  </div>
  <div>
    <label class="label">Phone</label>
    <input class="input" type="text" name="shipping_phone" value="{{ old('shipping_phone', auth()->user()->phone) }}" placeholder="Enter your phone number" required>
    @error('shipping_phone')
      <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
  </div>
  <div>
    <label class="label">Address</label>
    <textarea class="input" name="shipping_address" rows="4" placeholder="Enter your shipping address" required>{{ old('shipping_address', auth()->user()->address) }}</textarea>
    @error('shipping_address')
      <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
  </div>
  <button type="submit" class="btn-primary w-full">Proceed to Checkout</button>
</form>

<div class="card p-4 mt-6">
  <h2 class="font-semibold mb-2">Order Summary</h2>
  <ul class="space-y-2">
    @foreach($items as $item)
      <li class="flex justify-between text-sm">
        <span>{{ $item['name'] }} × {{ $item['qty'] }}</span>
        <span>LKR {{ number_format($item['price'] * $item['qty'], 2) }}</span>
      </li>
    @endforeach
  </ul>
  <div class="border-t mt-3 pt-3 flex justify-between font-semibold">
    <span>Total</span>
    <span>LKR {{ number_format($total, 2) }}</span>
  </div>
</div>
@endsection
