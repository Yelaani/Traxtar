<div>
    <a class="btn mb-4 inline-block" href="{{ route('admin.dashboard') }}">← Back to Dashboard</a>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">All Products</h1>
        <a href="{{ route('admin.products.create') }}" class="btn-primary">New Product</a>
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

    <!-- Search and Filter Section -->
    <div class="card p-4 mb-6">
        <div class="grid md:grid-cols-3 gap-4">
            <!-- Search Input -->
            <div>
                <label for="search" class="block text-sm font-medium text-neutral-700 mb-2">Search Products</label>
                <input 
                    type="text" 
                    id="search"
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by name, SKU, or description..."
                    class="w-full px-4 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category" class="block text-sm font-medium text-neutral-700 mb-2">Filter by Category</label>
                <select 
                    id="category"
                    wire:model.live="category" 
                    class="w-full px-4 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Categories</option>
                    <option value="women">Women</option>
                    <option value="men">Men</option>
                    <option value="accessories">Accessories</option>
                    <option value="gifts">Gifts</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-neutral-700 mb-2">Filter by Status</label>
                <select 
                    id="statusFilter"
                    wire:model.live="statusFilter" 
                    class="w-full px-4 py-2 border border-neutral-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="hidden">Hidden</option>
                </select>
            </div>
        </div>
    </div>

    @if($products->count() > 0)
        <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($products as $product)
                <li class="card overflow-hidden">
                    <div class="bg-neutral-100">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 flex items-center justify-center text-neutral-400 text-sm">No image</div>
                        @endif
                    </div>
                    <div class="p-4 space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold leading-tight">{{ $product->name }}</h3>
                            <span class="text-sm font-medium whitespace-nowrap">LKR {{ number_format($product->price, 2) }}</span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap text-xs">
                            <!-- Status Badge -->
                            @if($product->status === 'active')
                                <span class="px-2 py-1 text-green-800 bg-green-100 rounded-full font-medium">
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-1 text-gray-800 bg-gray-100 rounded-full font-medium">
                                    Hidden
                                </span>
                            @endif
                            <!-- Stock Badge -->
                            @if($product->stock < 5)
                                <span class="px-2 py-1 text-red-800 bg-red-100 rounded-full font-medium">
                                    Low: {{ $product->stock }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-blue-800 bg-blue-100 rounded-full font-medium">
                                    Stock: {{ $product->stock }}
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-neutral-500">
                            Created: {{ $product->created_at->format('M d, Y') }}
                        </div>
                        <div class="flex gap-2 pt-2">
                            <a class="btn" href="{{ route('admin.products.edit', $product->id) }}">Edit</a>
                            <button 
                                wire:click="deleteProduct({{ $product->id }})"
                                wire:confirm="Are you sure you want to delete this product? This action can be undone."
                                class="btn-danger"
                                type="button">
                                Delete
                            </button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @else
        <p class="text-neutral-600">No products found. <a class="underline" href="{{ route('admin.products.create') }}">Create one</a>.</p>
    @endif
</div>
