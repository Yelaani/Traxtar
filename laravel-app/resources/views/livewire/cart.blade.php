<div>
    <h1 class="text-2xl font-bold mb-6">Your Cart</h1>

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

    @if(empty($items))
        <p class="text-neutral-600">Your cart is empty.</p>
        <a class="btn mt-4 inline-block" href="{{ route('products.shop') }}">Continue shopping</a>
    @else
        <div class="space-y-4">
            <ul class="space-y-3">
                @foreach($items as $item)
                    <li class="card p-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            @if($item['image'])
                                <img src="{{ asset('storage/' . $item['image']) }}" class="w-16 h-16 rounded object-cover" alt="{{ $item['name'] }}">
                            @else
                                <div class="w-16 h-16 bg-neutral-200 rounded"></div>
                            @endif
                            <div>
                                <div class="font-semibold">{{ $item['name'] }}</div>
                                <div class="text-sm text-neutral-600">LKR {{ number_format($item['price'], 2) }}</div>
                                <div class="text-xs text-neutral-500">Stock: {{ $item['stock'] }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <input 
                                type="number" 
                                wire:change="updateQuantity({{ $item['id'] }}, $event.target.value)"
                                value="{{ $item['qty'] }}" 
                                min="0" 
                                max="{{ $item['stock'] }}"
                                class="input w-20">
                            <button 
                                wire:click="removeItem({{ $item['id'] }})"
                                class="btn-danger"
                                type="button">
                                Remove
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="flex items-center justify-between pt-2 border-t">
                <div class="text-lg font-semibold">Total: LKR {{ number_format($total, 2) }}</div>
                <div class="flex gap-2">
                    <button 
                        wire:click="clearCart"
                        class="btn"
                        type="button">
                        Clear Cart
                    </button>
                    <a class="btn-primary" href="{{ route('cart.checkout') }}">Checkout</a>
                </div>
            </div>
        </div>
    @endif
</div>
