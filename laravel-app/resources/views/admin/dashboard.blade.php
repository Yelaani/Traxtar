@extends('layouts.traxtar')

@section('content')
<div class="flex items-center justify-between mb-4">
  <a class="btn inline-block" href="{{ route('home') }}">← Back to Home</a>
  <form method="POST" action="{{ route('logout') }}" class="inline">
    @csrf
    <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white">
      <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
      </svg>
      Logout
    </button>
  </form>
</div>

<h1 class="text-2xl font-bold mb-4">Admin Dashboard</h1>

<div class="grid gap-6 md:grid-cols-3 mb-6">
  <div class="card p-6">
    <h3 class="font-semibold mb-2">Total Products</h3>
    <p class="text-2xl font-bold">{{ \App\Models\Product::count() }}</p>
  </div>
  <div class="card p-6">
    <h3 class="font-semibold mb-2">Total Orders</h3>
    <p class="text-2xl font-bold">{{ \App\Models\Order::count() }}</p>
  </div>
  <div class="card p-6">
    <h3 class="font-semibold mb-2">Pending Orders</h3>
    <p class="text-2xl font-bold text-orange-600">{{ \App\Models\Order::pending()->count() }}</p>
  </div>
</div>
<div class="grid gap-6 md:grid-cols-2 mb-6">
  <div class="card p-6">
    <h3 class="font-semibold mb-2">Total Customers</h3>
    <p class="text-2xl font-bold">{{ \App\Models\User::where('role', 'customer')->count() }}</p>
  </div>
  <div class="card p-6">
    <h3 class="font-semibold mb-2">Total Revenue</h3>
    <p class="text-2xl font-bold text-green-600">LKR {{ number_format(\App\Models\Order::where('status', '!=', 'cancelled')->sum('total'), 2) }}</p>
  </div>
</div>
@endsection
