<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseApiController
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        // Search filter
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Stock filter
        if ($request->has('in_stock') && $request->in_stock) {
            $query->inStock();
        }

        $perPage = min($request->get('per_page', 15), 100); // Max 100 per page
        $products = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'items' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created product (Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('admin-access');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|integer',
            'image' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        return $this->successResponse($product, 'Product created successfully', 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): JsonResponse
    {
        return $this->successResponse($product);
    }

    /**
     * Update the specified product (Admin only).
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorize('admin-access');

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'category_id' => 'nullable|integer',
            'image' => 'nullable|string',
        ]);

        $product->update($validated);
        $product->refresh();

        return $this->successResponse($product, 'Product updated successfully');
    }

    /**
     * Remove the specified product (Admin only).
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('admin-access');

        // Delete image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return $this->successResponse(null, 'Product deleted successfully');
    }
}
