<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\Product;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of all orders with filters.
     */
    public function index(Request $request): View
    {
        $this->authorize('admin-access');

        $query = Order::with(['user', 'items.product', 'payment'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order ID, customer name, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('shipping_name', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(15)->withQueryString();

        $statusCounts = [
            'pending' => Order::pending()->count(),
            'confirmed' => Order::confirmed()->count(),
            'shipped' => Order::shipped()->count(),
            'delivered' => Order::delivered()->count(),
            'cancelled' => Order::cancelled()->count(),
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Display the specified order details.
     */
    public function show(Order $order): View
    {
        $this->authorize('admin-access');

        $order->load(['user', 'items.product', 'payment']);

        // Get latest payment
        $latestPayment = $order->payment->sortByDesc('created_at')->first();
        $hasSuccessfulPayment = $order->payment->where('status', 'succeeded')->isNotEmpty();

        return view('admin.orders.show', compact('order', 'latestPayment', 'hasSuccessfulPayment'));
    }

    /**
     * Update the order status.
     * Status updates are idempotent to prevent duplicate stock reductions.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        $oldStatus = $order->status;
        $newStatus = $request->validated()['status'];

        // Idempotent: if status is already the same, just return
        if ($oldStatus === $newStatus) {
            return redirect()->route('admin.orders.show', $order)
                ->with('info', 'Order status is already ' . $newStatus . '.');
        }

        DB::beginTransaction();
        try {
            // If confirming, validate and reduce stock
            if ($newStatus === 'confirmed' && !$order->hasStockReduced()) {
                $this->reduceStockForOrder($order);
            }

            // If cancelling and stock was reduced, restore it
            if ($newStatus === 'cancelled' && $order->hasStockReduced()) {
                $this->restoreStockForOrder($order);
            }

            // Update order status
            $order->update(['status' => $newStatus]);

            // Log the status change
            $this->logActivity(
                'order.status.changed',
                $order,
                'Order Management',
                'info',
                "Order #{$order->id} status changed from '{$oldStatus}' to '{$newStatus}'",
                [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'order_id' => $order->id,
                    'order_total' => $order->total,
                    'changed_by' => auth()->id(),
                ]
            );

            DB::commit();

            return redirect()->route('admin.orders.show', $order)
                ->with('success', "Order status updated to '{$newStatus}' successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the order and restore stock if needed.
     */
    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        if ($order->isCancelled()) {
            return redirect()->route('admin.orders.show', $order)
                ->with('info', 'Order is already cancelled.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;

            // Restore stock if it was reduced
            if ($order->hasStockReduced()) {
                $this->restoreStockForOrder($order);
            }

            // Update order status
            $order->update(['status' => 'cancelled']);

            // Log the cancellation
            $this->logActivity(
                'order.cancelled',
                $order,
                'Order Management',
                'warning',
                "Order #{$order->id} was cancelled",
                [
                    'old_status' => $oldStatus,
                    'order_id' => $order->id,
                    'order_total' => $order->total,
                    'cancelled_by' => auth()->id(),
                ]
            );

            DB::commit();

            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    /**
     * Reduce stock for all items in the order.
     */
    private function reduceStockForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = Product::lockForUpdate()->find($item->product_id);

            if (!$product) {
                throw new \Exception("Product #{$item->product_id} not found.");
            }

            // Validate stock availability
            if ($product->stock < $item->qty) {
                throw new \Exception(
                    "Insufficient stock for '{$product->name}'. " .
                    "Required: {$item->qty}, Available: {$product->stock}"
                );
            }

            // Reduce stock
            $product->decrement('stock', $item->qty);
            $product->refresh(); // Refresh to get updated stock value

            // Log stock reduction
            $this->logActivity(
                'order.stock.reduced',
                $product,
                'Inventory Management',
                'info',
                "Stock reduced for '{$product->name}' (Order #{$order->id})",
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity_reduced' => $item->qty,
                    'old_stock' => $product->stock + $item->qty,
                    'new_stock' => $product->stock,
                ]
            );
        }
    }

    /**
     * Restore stock for all items in the order.
     */
    private function restoreStockForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = Product::lockForUpdate()->find($item->product_id);

            if ($product) {
                $oldStock = $product->stock;
                $product->increment('stock', $item->qty);
                $product->refresh(); // Refresh to get updated stock value

                // Log stock restoration
                $this->logActivity(
                    'order.stock.restored',
                    $product,
                    'Inventory Management',
                    'info',
                    "Stock restored for '{$product->name}' (Order #{$order->id})",
                    [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity_restored' => $item->qty,
                        'old_stock' => $oldStock,
                        'new_stock' => $product->stock,
                    ]
                );
            }
        }
    }
}
