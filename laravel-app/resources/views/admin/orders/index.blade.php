@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('admin.dashboard') }}">← Back to Dashboard</a>

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Order Management</h1>
</div>

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

<!-- Filters - Compact at top -->
<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-end gap-3">
        <!-- Search -->
        <div class="flex-1 min-w-[200px]">
            <label for="search" class="block text-xs font-medium text-neutral-700 mb-1">Search</label>
            <input 
                type="text" 
                id="search"
                name="search" 
                value="{{ request('search') }}"
                placeholder="Order ID, name, email..."
                class="w-full px-3 py-1.5 text-sm border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Status Filter -->
        <div class="min-w-[140px]">
            <label for="status" class="block text-xs font-medium text-neutral-700 mb-1">Status</label>
            <select 
                id="status"
                name="status" 
                class="w-full px-3 py-1.5 text-sm border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <!-- Date From -->
        <div class="min-w-[140px]">
            <label for="date_from" class="block text-xs font-medium text-neutral-700 mb-1">From Date</label>
            <input 
                type="date" 
                id="date_from"
                name="date_from" 
                value="{{ request('date_from') }}"
                class="w-full px-3 py-1.5 text-sm border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Date To -->
        <div class="min-w-[140px]">
            <label for="date_to" class="block text-xs font-medium text-neutral-700 mb-1">To Date</label>
            <input 
                type="date" 
                id="date_to"
                name="date_to" 
                value="{{ request('date_to') }}"
                class="w-full px-3 py-1.5 text-sm border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
            <button type="submit" class="btn text-sm px-4 py-1.5">Filter</button>
            <a href="{{ route('admin.orders.index') }}" class="btn bg-neutral-500 hover:bg-neutral-600 text-sm px-4 py-1.5">Clear</a>
        </div>
    </form>
</div>

<!-- Status Summary -->
<div class="flex flex-nowrap gap-2 mb-6">
    <div class="card p-3 text-center flex-1 min-w-0">
        <div class="text-xl font-bold text-orange-600">{{ $statusCounts['pending'] }}</div>
        <div class="text-xs text-neutral-600 mt-0.5">Pending</div>
    </div>
    <div class="card p-3 text-center flex-1 min-w-0">
        <div class="text-xl font-bold text-blue-600">{{ $statusCounts['confirmed'] }}</div>
        <div class="text-xs text-neutral-600 mt-0.5">Confirmed</div>
    </div>
    <div class="card p-3 text-center flex-1 min-w-0">
        <div class="text-xl font-bold text-purple-600">{{ $statusCounts['shipped'] }}</div>
        <div class="text-xs text-neutral-600 mt-0.5">Shipped</div>
    </div>
    <div class="card p-3 text-center flex-1 min-w-0">
        <div class="text-xl font-bold text-green-600">{{ $statusCounts['delivered'] }}</div>
        <div class="text-xs text-neutral-600 mt-0.5">Delivered</div>
    </div>
    <div class="card p-3 text-center flex-1 min-w-0">
        <div class="text-xl font-bold text-red-600">{{ $statusCounts['cancelled'] }}</div>
        <div class="text-xs text-neutral-600 mt-0.5">Cancelled</div>
    </div>
</div>

<!-- Orders Table -->
@if($orders->count() > 0)
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead class="bg-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Order #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-neutral-200">
                @foreach($orders as $order)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-neutral-900">#{{ $order->id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-neutral-900">{{ $order->user->name ?? 'N/A' }}</div>
                            <div class="text-xs text-neutral-500">{{ $order->user->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-neutral-600">{{ $order->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-neutral-500">{{ $order->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-neutral-900">{{ $order->formatted_total }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($order->status === 'pending')
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                            @elseif($order->status === 'confirmed')
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Confirmed</span>
                            @elseif($order->status === 'shipped')
                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">Shipped</span>
                            @elseif($order->status === 'delivered')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Delivered</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Cancelled</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $hasSuccessfulPayment = $order->payment->where('status', 'succeeded')->isNotEmpty();
                            @endphp
                            @if($hasSuccessfulPayment)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Paid</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Unpaid</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">View Details</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $orders->links() }}
    </div>
@else
    <div class="card p-8 text-center">
        <p class="text-neutral-600 mb-4">No orders found.</p>
    </div>
@endif
@endsection
