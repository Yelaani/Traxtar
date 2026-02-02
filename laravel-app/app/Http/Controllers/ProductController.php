<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class ProductController extends Controller
{
    use LogsActivity;
    /**
     * Display public product shop page.
     */
    public function shop(): View
    {
        $products = Product::latest()->get();
        
        return view('products.shop', compact('products'));
    }

    /**
     * Display a listing of products (Admin).
     */
    public function index(): View
    {
        $this->authorize('admin-access');
        
        $products = Product::latest()->get();
        
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $this->authorize('admin-access');
        
        return view('products.create');
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads', 'public');
            $validated['image'] = $path;
        }

        $product = Product::create($validated);

        // Log product creation
        $this->logProductCreated($product);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product (Public).
     */
    public function show(Product $product): View
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $this->authorize('admin-access');
        
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        // Store old values for audit logging
        $oldValues = [
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
            'status' => $product->status,
            'description' => $product->description,
            'sku' => $product->sku,
        ];

        $validated = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            $path = $request->file('image')->store('uploads', 'public');
            $validated['image'] = $path;
        }

        $product->update($validated);

        // Log product update with before/after values
        $newValues = [
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
            'status' => $product->status,
            'description' => $product->description,
            'sku' => $product->sku,
        ];
        $this->logProductUpdated($product, $oldValues, $newValues);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product (soft delete).
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Log product deletion before soft deleting
        $this->logProductDeleted($product);
        
        // Soft delete the product (image is kept for potential restore)
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
