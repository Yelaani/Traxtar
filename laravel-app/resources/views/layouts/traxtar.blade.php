<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <title>TRAXTAR</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>
<body class="font-inter bg-neutral-50 min-h-screen">

  <nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center flex-shrink-0">
          <a href="{{ route('home') }}" class="flex items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto">
          </a>
        </div>

        <!-- Admin Quick Links - Only visible for admin users -->
        @auth
          @if(auth()->user()->isAdmin())
            <div class="flex items-center justify-center flex-1 space-x-4 mx-8">
              <a href="{{ route('admin.dashboard') }}" 
                 class="flex items-center space-x-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 text-gray-900' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
              </a>
              <a href="{{ route('admin.analytics') }}" 
                 class="flex items-center space-x-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition {{ request()->routeIs('admin.analytics') ? 'bg-gray-100 text-gray-900' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Analytics</span>
              </a>
              <a href="{{ route('admin.products.index') }}" 
                 class="flex items-center space-x-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition {{ request()->routeIs('admin.products.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Products</span>
              </a>
              <a href="{{ route('admin.orders.index') }}" 
                 class="flex items-center space-x-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition {{ request()->routeIs('admin.orders.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Orders</span>
              </a>
              @can('viewAny', \App\Models\User::class)
                <a href="{{ route('admin.admins.index') }}" 
                   class="flex items-center space-x-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition {{ request()->routeIs('admin.admins.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                  </svg>
                  <span>Admins</span>
                </a>
              @endcan
            </div>
          @else
            <!-- Category Buttons - Only visible for non-admin users -->
            <div class="flex items-center justify-center flex-1 space-x-16 mx-8">
              <a href="{{ route('products.shop', ['category' => 'all']) }}" 
                 class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'all' || !request()->has('category') ? 'border-b-2 border-black' : '' }}">
                All Products
              </a>
              <a href="{{ route('products.shop', ['category' => 'women']) }}" 
                 class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'women' ? 'border-b-2 border-black' : '' }}">
                Women
              </a>
              <a href="{{ route('products.shop', ['category' => 'men']) }}" 
                 class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'men' ? 'border-b-2 border-black' : '' }}">
                Men
              </a>
              <a href="{{ route('products.shop', ['category' => 'accessories']) }}" 
                 class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'accessories' ? 'border-b-2 border-black' : '' }}">
                Accessories
              </a>
              <a href="{{ route('products.shop', ['category' => 'gifts']) }}" 
                 class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'gifts' ? 'border-b-2 border-black' : '' }}">
                Gifts
              </a>
            </div>
          @endif
        @else
          <!-- Guest users see category buttons -->
          <div class="flex items-center justify-center flex-1 space-x-16 mx-8">
            <a href="{{ route('products.shop', ['category' => 'all']) }}" 
               class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'all' || !request()->has('category') ? 'border-b-2 border-black' : '' }}">
              All Products
            </a>
            <a href="{{ route('products.shop', ['category' => 'women']) }}" 
               class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'women' ? 'border-b-2 border-black' : '' }}">
              Women
            </a>
            <a href="{{ route('products.shop', ['category' => 'men']) }}" 
               class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'men' ? 'border-b-2 border-black' : '' }}">
              Men
            </a>
            <a href="{{ route('products.shop', ['category' => 'accessories']) }}" 
               class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'accessories' ? 'border-b-2 border-black' : '' }}">
              Accessories
            </a>
            <a href="{{ route('products.shop', ['category' => 'gifts']) }}" 
               class="font-bold text-black hover:text-gray-600 transition whitespace-nowrap px-4 py-2 uppercase tracking-wide {{ request()->get('category') == 'gifts' ? 'border-b-2 border-black' : '' }}">
              Gifts
            </a>
          </div>
        @endauth

        <!-- Right side: Cart, Profile, Login/Register -->
        <div class="flex items-center space-x-4 flex-shrink-0">
          @auth
            @unless(auth()->user()->isAdmin())
              <!-- Cart Icon - Only for non-admin users -->
              <a href="{{ route('cart.index') }}" class="relative p-2 text-black hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @livewire('cart-counter')
              </a>
            @endunless

            <!-- Profile Icon -->
            <a href="{{ route('dashboard') }}" class="p-2 text-black hover:text-gray-600 transition">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </a>
          @else
            <!-- Guest: Login/Register -->
            <a href="{{ route('login') }}" class="text-black hover:text-gray-600 font-medium">Login</a>
            <a href="{{ route('register') }}" class="text-black hover:text-gray-600 font-medium">Register</a>
          @endauth
        </div>
      </div>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto p-4">
    @if(session('success'))
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
      </div>
    @endif

    @if($errors->any())
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @yield('content')
  </main>

  <footer class="text-center text-sm text-neutral-500 py-6">
    © 2025 Traxtar. All Rights Reserved.
  </footer>

  @stack('modals')
  @livewireScripts
</body>
</html>
