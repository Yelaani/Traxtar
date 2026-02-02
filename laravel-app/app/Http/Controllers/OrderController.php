<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Place order from cart.
     */
    public function placeOrder(Request $request): RedirectResponse
    {
        $this->authorize('customer-access');
        
        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
        ]);

        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Cart is empty.');
        }

        // Validate stock availability
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product || $product->stock < $item['qty']) {
                return redirect()->route('cart.checkout')
                    ->with('error', "Insufficient stock for {$item['name']}.");
            }
        }

        // Create order in transaction
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $total = $this->calculateTotal($cart);

            $order = Order::create([
                'user_id' => $user->id,
                'shipping_name' => $validated['shipping_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_address' => $validated['shipping_address'],
                'total' => $total,
                'status' => 'pending',
            ]);

            // Create order items (stock will be reduced when order is confirmed by admin)
            foreach ($cart as $productId => $item) {
                $product = Product::find($productId);
                
                $order->items()->create([
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);
            }

            DB::commit();
            
            // Clear cart
            session()->forget('cart');
            
            // Store order_id in session for payment
            session()->put('order_id', $order->id);
            
            // Redirect to payment checkout
            return redirect()->route('payment.checkout', ['order_id' => $order->id])
                ->with('success', 'Order placed successfully! Please complete payment.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('cart.checkout')
                ->with('error', 'Order failed. Please try again.');
        }
    }

    /**
     * Display user's orders.
     */
    public function index(): View
    {
        $this->authorize('customer-access');
        
        $orders = Order::forUser(auth()->id())
            ->latest()
            ->get();
        
        return view('orders.index', compact('orders'));
    }

    /**
     * Display specific order details.
     */
    public function show(Order $order): View
    {
        $user = auth()->user();
        
        // Only admins or order owner can view
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this order.');
        }
        
        $order->load('items.product', 'payment');
        
        // Get latest payment for the view
        $latestPayment = $order->payment->sortByDesc('created_at')->first();
        $hasSuccessfulPayment = $order->payment->where('status', 'succeeded')->isNotEmpty();
        $hasFailedPayment = $latestPayment && $latestPayment->status === 'failed';
        
        return view('orders.show', compact('order', 'latestPayment', 'hasSuccessfulPayment', 'hasFailedPayment'));
    }

    /**
     * Calculate total from cart.
     */
    private function calculateTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['qty'];
        }
        return $total;
    }
}
