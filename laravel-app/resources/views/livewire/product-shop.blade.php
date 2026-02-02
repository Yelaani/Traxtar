<div>
    <h1 class="text-2xl font-bold mb-6">Shop</h1>

    <!-- Search and Sort Controls -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <!-- Search -->
        <div class="flex-1 w-full sm:w-auto">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="Search products..."
                class="input w-full sm:w-64">
        </div>

        <!-- Sort -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-neutral-600">Sort by:</label>
            <select wire:model.live="sortBy" class="input">
                <option value="latest">Latest</option>
                <option value="name">Name (A-Z)</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
            </select>
        </div>
    </div>

    <!-- Products Grid -->
    @if($products->count() > 0)
        <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-6">
            @foreach($products as $product)
                <li class="card overflow-hidden hover:shadow-lg transition">
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
                            <span class="text-sm font-medium whitespace-nowrap">{{ $product->formatted_price }}</span>
                        </div>
                        @if($product->description)
                            <p class="text-sm text-neutral-600 line-clamp-2">{{ Str::limit($product->description, 80) }}</p>
                        @endif
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-neutral-600">
                                Stock: 
                                @if($product->stock > 0)
                                    <span class="text-green-600 font-medium">{{ $product->stock }}</span>
                                @else
                                    <span class="text-red-600 font-medium">Out of Stock</span>
                                @endif
                            </div>
                            <a class="btn" href="{{ route('products.show', $product) }}">View</a>
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
        <div class="text-center py-12">
            <p class="text-neutral-600 text-lg mb-4">
                @if($search)
                    No products found matching "{{ $search }}".
                @else
                    No products available yet.
                @endif
            </p>
            @if($search)
                <button wire:click="$set('search', '')" class="btn">Clear Search</button>
            @endif
        </div>
    @endif
</div>
