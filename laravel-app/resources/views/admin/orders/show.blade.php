@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('admin.orders.index') }}">← Back to Orders</a>

<h1 class="text-2xl font-bold mb-4">Order #{{ $order->id }}</h1>

@if(session('success'))
    <div class="bg-green-500 text-white p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-500 text-white p-4 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

@if(session('info'))
    <div class="bg-blue-500 text-white p-4 rounded mb-4">
        {{ session('info') }}
    </div>
@endif

<div class="grid md:grid-cols-3 gap-6 mb-6">
    <!-- Customer Details -->
    <div class="card p-4">
        <h2 class="font-semibold mb-3 text-lg">Customer Details</h2>
        <div class="text-sm space-y-2">
            <div>
                <span class="text-neutral-600">Name:</span>
                <div class="font-medium">{{ $order->user->name ?? 'N/A' }}</div>
            </div>
            <div>
                <span class="text-neutral-600">Email:</span>
                <div class="font-medium">{{ $order->user->email ?? 'N/A' }}</div>
            </div>
            <div>
                <span class="text-neutral-600">Phone:</span>
                <div class="font-medium">{{ $order->shipping_phone }}</div>
            </div>
        </div>
    </div>

    <!-- Shipping Information -->
    <div class="card p-4">
        <h2 class="font-semibold mb-3 text-lg">Shipping Information</h2>
        <div class="text-sm space-y-2">
            <div>
                <span class="text-neutral-600">Name:</span>
                <div class="font-medium">{{ $order->shipping_name }}</div>
            </div>
            <div>
                <span class="text-neutral-600">Phone:</span>
                <div class="font-medium">{{ $order->shipping_phone }}</div>
            </div>
            <div>
                <span class="text-neutral-600">Address:</span>
                <div class="font-medium whitespace-pre-line">{{ $order->shipping_address }}</div>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="card p-4">
        <h2 class="font-semibold mb-3 text-lg">Order Summary</h2>
        <div class="text-sm space-y-2">
            <div class="flex justify-between">
                <span class="text-neutral-600">Status:</span>
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
            <div class="flex justify-between font-semibold">
                <span>Total:</span>
                <span>{{ $order->formatted_total }}</span>
            </div>
            <div class="text-xs text-neutral-600 pt-2 border-t">
                Placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}
            </div>
            @if($latestPayment)
                <div class="flex justify-between pt-2 border-t">
                    <span class="text-neutral-600">Payment:</span>
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
</div>

<!-- Status Change Form -->
<div class="card p-4 mb-6">
    <h2 class="font-semibold mb-4 text-lg">Change Order Status</h2>
    
    <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="flex gap-4 items-end">
        @csrf
        @method('PATCH')
        
        <div class="flex-1">
            <label for="status" class="block text-sm font-medium text-neutral-700 mb-2">New Status</label>
            <select 
                id="status"
                name="status" 
                class="w-full px-4 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <p class="text-xs text-neutral-500 mt-1">
                Workflow: pending → confirmed → shipped → delivered
            </p>
        </div>
        
        <button type="submit" class="btn">Update Status</button>
    </form>
    
    @if(!$order->isCancelled())
        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="mt-4">
            @csrf
            <button type="submit" class="btn bg-red-600 hover:bg-red-700" onclick="return confirm('Are you sure you want to cancel this order? Stock will be restored if applicable.')">
                Cancel Order
            </button>
        </form>
    @endif
</div>

<!-- Order Items -->
<div class="card p-4 mb-6">
    <h2 class="font-semibold mb-4 text-lg">Order Items</h2>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-neutral-100">
                <th class="px-4 py-2 text-left">Product</th>
                <th class="px-4 py-2 text-left">Price</th>
                <th class="px-4 py-2 text-left">Quantity</th>
                <th class="px-4 py-2 text-left">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr class="border-b">
                <td class="px-4 py-2">
                    <div class="font-medium">{{ $item->product_name }}</div>
                    @if($item->product)
                        <div class="text-xs text-neutral-500">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                    @endif
                </td>
                <td class="px-4 py-2">LKR {{ number_format($item->price, 2) }}</td>
                <td class="px-4 py-2">{{ $item->qty }}</td>
                <td class="px-4 py-2 font-medium">{{ $item->formatted_subtotal }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-semibold bg-neutral-50">
                <td colspan="3" class="px-4 py-2 text-right">Total:</td>
                <td class="px-4 py-2">{{ $order->formatted_total }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Payment Information -->
@if($order->payment->count() > 0)
    <div class="card p-4">
        <h2 class="font-semibold mb-4 text-lg">Payment History</h2>
        <div class="space-y-3">
            @foreach($order->payment->sortByDesc('created_at') as $payment)
                <div class="border rounded p-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-medium">Payment #{{ $payment->id }}</div>
                            <div class="text-sm text-neutral-600">{{ $payment->created_at->format('F d, Y \a\t g:i A') }}</div>
                        </div>
                        <div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($payment->status === 'succeeded') bg-green-100 text-green-800
                                @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                @else bg-neutral-100 text-neutral-800
                                @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                    @if($payment->amount)
                        <div class="text-sm mt-2">
                            <span class="text-neutral-600">Amount:</span>
                            <span class="font-medium">LKR {{ number_format($payment->amount / 100, 2) }}</span>
                        </div>
                    @endif
                    @if($payment->failure_reason)
                        <div class="text-sm mt-2 text-red-600">
                            <span class="font-medium">Failure Reason:</span> {{ $payment->failure_reason }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection
