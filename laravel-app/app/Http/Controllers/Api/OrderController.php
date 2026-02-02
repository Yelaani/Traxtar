<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends BaseApiController
{
    /**
     * Display a listing of user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('customer-access');

        $perPage = $request->get('per_page', 15);
        $orders = Order::forUser(auth()->id())
            ->with('items.product')
            ->latest()
            ->paginate($perPage);

        return $this->successResponse([
            'items' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('customer-access');

        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        // Validate stock and calculate total
        $total = 0;
        $orderItems = [];

        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);
            
            if ($product->stock < $item['qty']) {
                return $this->errorResponse(
                    "Insufficient stock for {$product->name} (available: {$product->stock})",
                    400
                );
            }

            $subtotal = $product->price * $item['qty'];
            $total += $subtotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'qty' => $item['qty'],
            ];
        }

        // Create order in transaction
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'shipping_name' => $validated['shipping_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_address' => $validated['shipping_address'],
                'total' => $total,
                'status' => 'pending',
            ]);

            // Create order items (stock will be reduced when order is confirmed by admin)
            foreach ($orderItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            return $this->successResponse(
                $order->load('items.product'),
                'Order created successfully',
                201
            );
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            
            Log::error('Order creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse(
                'Order failed. Please try again.',
                500
            );
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        $user = auth()->user();

        // Check authorization
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return $this->unauthorizedResponse();
        }

        $order->load('items.product');

        return $this->successResponse($order);
    }

    /**
     * Display a listing of all orders for admin (analytics).
     * Protected via Sanctum tokens and admin middleware.
     */
    public function adminIndex(Request $request): JsonResponse
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

        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        // Calculate status counts
        $statusCounts = [
            'pending' => Order::pending()->count(),
            'confirmed' => Order::confirmed()->count(),
            'shipped' => Order::shipped()->count(),
            'delivered' => Order::delivered()->count(),
            'cancelled' => Order::cancelled()->count(),
        ];

        return $this->successResponse([
            'items' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
            'status_counts' => $statusCounts,
        ]);
    }
}
