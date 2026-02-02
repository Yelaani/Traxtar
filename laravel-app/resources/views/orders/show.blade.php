@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('orders.index') }}">← Back to orders</a>

<h1 class="text-2xl font-bold mb-4">Order #{{ $order->id }}</h1>

<div class="grid md:grid-cols-2 gap-6">
  <div class="card p-4">
    <h2 class="font-semibold mb-2">Shipping</h2>
    <div class="text-sm">
      <div>{{ $order->shipping_name }}</div>
      <div>{{ $order->shipping_phone }}</div>
      <div class="mt-1 whitespace-pre-line">{{ $order->shipping_address }}</div>
    </div>
  </div>
  <div class="card p-4">
    <h2 class="font-semibold mb-2">Summary</h2>
    <div class="text-sm flex justify-between">
      <span>Status</span>
      <span class="px-2 py-1 rounded-full text-xs font-semibold
        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
        @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
        @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
        @elseif($order->status === 'delivered') bg-green-100 text-green-800
        @else bg-red-100 text-red-800
        @endif">
        {{ ucfirst($order->status) }}
      </span>
    </div>
    <div class="text-sm flex justify-between font-semibold mt-2">
      <span>Total</span>
      <span>{{ $order->formatted_total }}</span>
    </div>
    <div class="text-xs text-neutral-600 mt-2">
      Placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}
    </div>
    @if($latestPayment)
      <div class="text-sm flex justify-between mt-2 pt-2 border-t">
        <span>Payment Status</span>
        <span class="px-2 py-1 rounded-full text-xs font-semibold
          @if($latestPayment->status === 'succeeded') bg-green-100 text-green-800
          @elseif($latestPayment->status === 'pending') bg-yellow-100 text-yellow-800
          @elseif($latestPayment->status === 'failed') bg-red-100 text-red-800
          @else bg-neutral-100 text-neutral-800
          @endif">
          {{ ucfirst($latestPayment->status) }}
        </span>
      </div>
    @endif
  </div>
</div>

@if($order->status === 'pending' && auth()->user()->isCustomer() && $order->user_id === auth()->id())
  <div class="card p-4 mt-6">
    <h2 class="font-semibold mb-4">Payment</h2>
    
    @if($hasSuccessfulPayment)
      <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded mb-4">
        <p class="font-semibold">✓ Payment completed successfully!</p>
        <p class="text-sm mt-1">Your order is being processed.</p>
      </div>
    @elseif($hasFailedPayment)
      <div class="bg-red-100 border border-red-300 text-red-800 p-4 rounded mb-4">
        <p class="font-semibold">✗ Payment failed</p>
        @if($latestPayment->failure_reason)
          <p class="text-sm mt-1">{{ $latestPayment->failure_reason }}</p>
        @else
          <p class="text-sm mt-1">Please try again or use a different payment method.</p>
        @endif
      </div>
      <form method="POST" action="{{ route('payment.checkout') }}" class="mt-4">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <button type="submit" class="btn-primary">Retry Payment</button>
      </form>
    @else
      <p class="text-sm text-neutral-600 mb-4">Complete your payment to proceed with the order.</p>
      <form method="POST" action="{{ route('payment.checkout') }}">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <button type="submit" class="btn-primary">Pay Now</button>
      </form>
    @endif
  </div>
@endif

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
      @foreach($order->items as $item)
      <tr class="border-b">
        <td class="px-3 py-2">{{ $item->product_name }}</td>
        <td class="px-3 py-2">LKR {{ number_format($item->price, 2) }}</td>
        <td class="px-3 py-2">{{ $item->qty }}</td>
        <td class="px-3 py-2">{{ $item->formatted_subtotal }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="font-semibold">
        <td colspan="3" class="px-3 py-2 text-right">Total:</td>
        <td class="px-3 py-2">{{ $order->formatted_total }}</td>
      </tr>
    </tfoot>
  </table>
</div>
@endsection
