@extends('layouts.traxtar')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Failure Message -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h1 class="text-2xl font-bold text-red-800">Payment Failed</h1>
                <p class="text-red-700 mt-1">
                    {{ $failureReason ?? 'Your payment could not be processed. Please try again or contact support if the problem persists.' }}
                </p>
            </div>
        </div>
    </div>

    @if($payment && $payment->failure_reason)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-800">
                <strong>Reason:</strong> {{ $payment->failure_reason }}
            </p>
        </div>
    @endif

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
                <span class="font-medium text-red-600">{{ ucfirst($payment->status ?? 'Failed') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Items:</span>
                <span>{{ $order->items->count() }} item(s)</span>
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
                <span class="text-xl font-bold">LKR {{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Helpful Information -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-blue-800 mb-2">What to do next?</h3>
        <ul class="list-disc list-inside text-sm text-blue-700 space-y-1">
            <li>Check that your payment method has sufficient funds</li>
            <li>Verify your card details are correct</li>
            <li>Try using a different payment method</li>
            <li>Contact your bank if the issue persists</li>
            <li>Contact our support team if you need assistance</li>
        </ul>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4">
        <form action="{{ route('payment.checkout') }}" method="POST" class="flex-1">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <button type="submit" class="btn-primary w-full">Try Again</button>
        </form>
        <a href="{{ route('orders.show', $order) }}" class="btn flex-1 text-center">
            View Order
        </a>
        <a href="{{ route('cart.index') }}" class="btn flex-1 text-center">
            Back to Cart
        </a>
    </div>
</div>
@endsection
