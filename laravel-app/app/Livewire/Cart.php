<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class Cart extends Component
{
    public $items = [];
    public $total = 0;

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        $cart = session()->get('cart', []);
        $this->items = [];
        $this->total = 0;

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $this->items[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'qty' => $item['qty'],
                    'image' => $product->image,
                    'stock' => $product->stock,
                ];
                $this->total += $product->price * $item['qty'];
            }
        }
    }

    public function updateQuantity($productId, $qty)
    {
        $qty = (int) $qty;
        
        if ($qty <= 0) {
            $this->removeItem($productId);
            return;
        }

        $product = Product::find($productId);
        if (!$product) {
            session()->flash('error', 'Product not found.');
            $this->loadCart();
            return;
        }

        if ($product->stock < $qty) {
            session()->flash('error', "Insufficient stock for {$product->name} (available: {$product->stock}).");
            $this->loadCart();
            return;
        }

        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] = $qty;
            session()->put('cart', $cart);
            session()->flash('success', 'Cart updated.');
        }

        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function removeItem($productId)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            session()->flash('success', 'Item removed.');
        }

        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function clearCart()
    {
        session()->forget('cart');
        session()->flash('success', 'Cart cleared.');
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
