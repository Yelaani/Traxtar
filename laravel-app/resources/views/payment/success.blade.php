@extends('layouts.traxtar')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Success Message -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h1 class="text-2xl font-bold text-green-800">Payment Successful!</h1>
                <p class="text-green-700 mt-1">Your payment has been processed successfully. Your order is being prepared.</p>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="card p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
        
        <div class="space-y-3 mb-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Order ID:</span>
                <span class="font-medium">#{{ $order->id }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Payment Status:</span>
                <span class="font-medium text-green-600">{{ ucfirst($payment->status) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Items:</span>
                <span>{{ $order->items->count() }} item(s)</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Payment Date:</span>
                <span>{{ $payment->updated_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div class="border-t pt-4 mb-4">
            <h3 class="font-semibold mb-3">Order Items</h3>
            <ul class="space-y-3">
                @foreach($order->items as $item)
                    <li class="flex justify-between items-center">
                        <div class="flex-1">
                            <span class="font-medium">{{ $item->product_name }}</span>
                            <span class="text-gray-600 text-sm ml-2">× {{ $item->qty }}</span>
                        </div>
                        <span class="font-medium">LKR {{ number_format($item->price * $item->qty, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="border-t pt-4">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold">Total Amount:</span>
                <span class="text-xl font-bold text-green-600">LKR {{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Shipping Information -->
    <div class="card p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Shipping Information</h2>
        <div class="space-y-2 text-sm">
            <div>
                <span class="text-gray-600">Name:</span>
                <span class="ml-2 font-medium">{{ $order->shipping_name }}</span>
            </div>
            <div>
                <span class="text-gray-600">Phone:</span>
                <span class="ml-2 font-medium">{{ $order->shipping_phone }}</span>
            </div>
            <div>
                <span class="text-gray-600">Address:</span>
                <span class="ml-2 font-medium">{{ $order->shipping_address }}</span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4">
        <a href="{{ route('orders.show', $order) }}" class="btn-primary flex-1 text-center">
            View Order Details
        </a>
        <a href="{{ route('products.shop') }}" class="btn flex-1 text-center">
            Continue Shopping
        </a>
    </div>
</div>
@endsection
