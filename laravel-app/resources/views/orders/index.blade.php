@extends('layouts.traxtar')

@section('content')
<a class="btn mb-4 inline-block" href="{{ route('customer.dashboard') }}">← Back to dashboard</a>

<h1 class="text-2xl font-bold mb-6">My Orders</h1>

@if($orders->count() > 0)
  <table class="w-full text-sm">
    <thead>
      <tr class="bg-neutral-100">
        <th class="px-3 py-2 text-left">Order #</th>
        <th class="px-3 py-2 text-left">Date</th>
        <th class="px-3 py-2 text-left">Total</th>
        <th class="px-3 py-2 text-left">Status</th>
        <th class="px-3 py-2"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($orders as $order)
      <tr class="border-b">
        <td class="px-3 py-2">{{ $order->id }}</td>
        <td class="px-3 py-2">{{ $order->created_at->format('M d, Y') }}</td>
        <td class="px-3 py-2">{{ $order->formatted_total }}</td>
        <td class="px-3 py-2">
          <span class="px-2 py-1 rounded-full text-xs font-semibold
            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
            @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
            @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
            @elseif($order->status === 'delivered') bg-green-100 text-green-800
            @else bg-red-100 text-red-800
            @endif">
            {{ ucfirst($order->status) }}
          </span>
        </td>
        <td class="px-3 py-2 text-right">
          <a class="btn" href="{{ route('orders.show', $order) }}">View</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
@else
  <p class="text-neutral-600">No orders yet.</p>
  <a class="btn mt-4 inline-block" href="{{ route('products.shop') }}">Shop now</a>
@endif
@endsection
