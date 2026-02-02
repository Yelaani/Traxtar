@extends('layouts.traxtar')

@section('content')
<h1 class="text-3xl font-heading font-bold mb-6">
  Welcome, {{ auth()->user()->name }}
</h1>

<!-- Stats Cards -->
<div class="grid gap-6 md:grid-cols-3 mt-6">
  <!-- Role -->
  <div class="card p-6 text-center hover:shadow-md transition">
    <div class="text-brand-600 mb-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
    </div>
    <div class="text-sm text-neutral-600">Your Role</div>
    <div class="text-xl font-heading font-bold mt-1">Customer</div>
  </div>

  <!-- Orders -->
  <div class="card p-6 text-center hover:shadow-md transition">
    <div class="text-brand-600 mb-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
    </div>
    <div class="text-sm text-neutral-600">Total Orders</div>
    <div class="text-xl font-heading font-bold mt-1">{{ auth()->user()->orders()->count() }}</div>
  </div>

  <!-- Cart -->
  <div class="card p-6 text-center hover:shadow-md transition">
    <div class="text-brand-600 mb-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
    </div>
    <div class="text-sm text-neutral-600">Cart Items</div>
    <div class="text-xl font-heading font-bold mt-1">
      {{ $cartCount ?? 0 }}
    </div>
  </div>
</div>

<!-- My Orders Section -->
<div class="card p-6 mt-8">
  <h2 class="font-heading text-xl font-bold mb-4">My Orders</h2>

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
</div>

<!-- Profile Section -->
<div class="card p-6 mt-8">
  <h2 class="font-heading text-xl font-bold mb-4">Profile</h2>
  <div class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-neutral-700 mb-1">Name</label>
      <p class="text-neutral-900">{{ $user->name }}</p>
    </div>
    <div>
      <label class="block text-sm font-medium text-neutral-700 mb-1">Email</label>
      <p class="text-neutral-900">{{ $user->email }}</p>
    </div>
    <div class="pt-4">
      <a href="{{ route('customer.profile') }}" class="btn-primary">Edit Profile</a>
    </div>
  </div>
</div>

<!-- Logout Section -->
<div class="card p-6 mt-8">
  <h2 class="font-heading text-xl font-bold mb-4">Account</h2>
  <form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white">
      Logout
    </button>
  </form>
</div>
@endsection
