<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get cart from session.
     */
    private function getCart(): array
    {
        return session()->get('cart', []);
    }

    /**
     * Save cart to session.
     */
    private function saveCart(array $cart): void
    {
        session()->put('cart', $cart);
    }

    /**
     * Display cart page.
     */
    public function index(): View
    {
        $items = $this->getCartItems();
        $total = $this->calculateTotal($items);
        
        return view('cart.index', compact('items', 'total'));
    }

    /**
     * Add product to cart.
     */
    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $qty = (int) $request->qty;

        // Check stock
        if ($product->stock < $qty) {
            return redirect()->route('products.shop')
                ->with('error', "Insufficient stock for {$product->name} (available: {$product->stock}).");
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

        return redirect()->route('cart.index')
            ->with('success', 'Item added to cart.');
    }

    /**
     * Update cart quantities.
     */
    public function update(Request $request): RedirectResponse
    {
        $cart = $this->getCart();
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Cart is empty.');
        }

        // Handle clear cart
        if ($request->has('clear')) {
            session()->forget('cart');
            return redirect()->route('cart.index')
                ->with('success', 'Cart cleared.');
        }

        // Handle remove item
        if ($request->has('remove')) {
            $productId = (int) $request->remove;
            unset($cart[$productId]);
            $this->saveCart($cart);
            
            return redirect()->route('cart.index')
                ->with('success', 'Item removed.');
        }

        // Handle update quantities
        if ($request->has('qty') && is_array($request->qty)) {
            foreach ($request->qty as $productId => $qty) {
                $productId = (int) $productId;
                $qty = (int) $qty;

                if ($qty <= 0) {
                    unset($cart[$productId]);
                    continue;
                }

                if (!isset($cart[$productId])) {
                    continue;
                }

                $product = Product::find($productId);
                if (!$product || $product->stock < $qty) {
                    return redirect()->route('cart.index')
                        ->with('error', "Insufficient stock for {$cart[$productId]['name']}.");
                }

                $cart[$productId]['qty'] = $qty;
            }

            $this->saveCart($cart);
            
            return redirect()->route('cart.index')
                ->with('success', 'Cart updated.');
        }

        return redirect()->route('cart.index');
    }

    /**
     * Show checkout form.
     */
    public function checkout(): View
    {
        $items = $this->getCartItems();
        
        if (empty($items)) {
            return redirect()->route('cart.index')
                ->with('error', 'Cart is empty.');
        }
        
        $total = $this->calculateTotal($items);
        
        return view('checkout.form', compact('items', 'total'));
    }

    /**
     * Get cart items with product details.
     */
    private function getCartItems(): array
    {
        $cart = $this->getCart();
        $items = [];

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $items[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'qty' => $item['qty'],
                    'image' => $product->image,
                ];
            }
        }

        return $items;
    }

    /**
     * Calculate total price.
     */
    private function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['qty'];
        }
        return $total;
    }
}
