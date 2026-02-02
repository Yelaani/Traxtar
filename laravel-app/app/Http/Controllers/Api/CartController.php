<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CartController extends BaseApiController
{
    /**
     * Get cart storage key for current user.
     */
    private function getCartKey(): string
    {
        $userId = auth()->id();
        return "api_cart_user_{$userId}";
    }

    /**
     * Get cart items from cache (for API) or session (for web).
     */
    private function getCart(): array
    {
        // For API requests, use cache keyed by user ID
        if (request()->is('api/*') || request()->expectsJson()) {
            $key = $this->getCartKey();
            return Cache::get($key, []);
        }
        
        // For web requests, use session
        return session()->get('cart', []);
    }

    /**
     * Save cart to cache (for API) or session (for web).
     */
    private function saveCart(array $cart): void
    {
        // For API requests, use cache keyed by user ID
        if (request()->is('api/*') || request()->expectsJson()) {
            $key = $this->getCartKey();
            Cache::put($key, $cart, now()->addDays(7)); // Store for 7 days
        } else {
            // For web requests, use session
            session()->put('cart', $cart);
        }
    }

    /**
     * Get cart items with product details.
     */
    private function getCartItems(): array
    {
        $cart = $this->getCart();
        $items = [];
        $total = 0;

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $subtotal = $product->price * $item['qty'];
                $total += $subtotal;
                
                $items[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'qty' => $item['qty'],
                    'image' => $product->image,
                    'stock' => $product->stock,
                    'subtotal' => $subtotal,
                ];
            }
        }

        return ['items' => $items, 'total' => $total];
    }

    /**
     * Get cart items.
     */
    public function index(Request $request): JsonResponse
    {
        $cartData = $this->getCartItems();
        
        return $this->successResponse($cartData);
    }

    /**
     * Add item to cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = (int) $validated['qty'];

        // Check stock
        if ($product->stock < $qty) {
            return $this->errorResponse(
                "Insufficient stock for {$product->name} (available: {$product->stock})",
                400
            );
        }

        $cart = $this->getCart();
        
        if (!isset($cart[$product->id])) {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 0,
                'image' => $product->image,
            ];
        }
        
        $cart[$product->id]['qty'] += $qty;
        $this->saveCart($cart);

        $cartData = $this->getCartItems();

        return $this->successResponse($cartData, 'Item added to cart', 201);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:0',
        ]);

        $productId = (int) $id;
        $qty = (int) $validated['qty'];

        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return $this->notFoundResponse('Product in cart');
        }

        if ($qty <= 0) {
            // Remove item if quantity is 0
            unset($cart[$productId]);
            $this->saveCart($cart);
            
            return $this->successResponse($this->getCartItems(), 'Item removed from cart');
        }

        $product = Product::find($productId);
        if (!$product) {
            return $this->notFoundResponse('Product');
        }

        if ($product->stock < $qty) {
            return $this->errorResponse(
                "Insufficient stock (available: {$product->stock})",
                400
            );
        }

        $cart[$productId]['qty'] = $qty;
        $this->saveCart($cart);

        return $this->successResponse($this->getCartItems(), 'Cart updated');
    }

    /**
     * Remove item from cart.
     */
    public function destroy($id): JsonResponse
    {
        $productId = (int) $id;
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return $this->notFoundResponse('Product in cart');
        }

        unset($cart[$productId]);
        $this->saveCart($cart);

        return $this->successResponse($this->getCartItems(), 'Item removed from cart');
    }
}
