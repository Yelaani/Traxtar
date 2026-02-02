<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public $productId = null;
    public $name = '';
    public $sku = '';
    public $description = '';
    public $price = '';
    public $stock = 0;
    public $category_id = null;
    public $status = 'active';
    public $image = null;
    public $existingImage = null;

    public function mount($productId = null)
    {
        if ($productId) {
            $product = Product::findOrFail($productId);
            $this->productId = $product->id;
            $this->name = $product->name;
            $this->sku = $product->sku ?? '';
            $this->description = $product->description ?? '';
            $this->price = $product->price;
            $this->stock = $product->stock;
            $this->category_id = $product->category_id;
            $this->status = $product->status ?? 'active';
            $this->existingImage = $product->image;
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|integer',
            'status' => 'required|in:active,hidden',
            'image' => $this->productId ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048' : 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'sku' => $this->sku ?: null,
            'description' => $this->description ?: null,
            'price' => $this->price,
            'stock' => $this->stock,
            'category_id' => $this->category_id ?: null,
            'status' => $this->status,
        ];

        // Handle image upload
        if ($this->image) {
            // Delete old image if editing
            if ($this->productId && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            
            $path = $this->image->store('uploads', 'public');
            $data['image'] = $path;
        } elseif ($this->productId) {
            // Keep existing image if not uploading new one
            $data['image'] = $this->existingImage;
        }

        if ($this->productId) {
            // Update existing product
            $product = Product::findOrFail($this->productId);
            $product->update($data);
            session()->flash('success', 'Product updated successfully.');
            return redirect()->route('admin.products.index');
        } else {
            // Create new product
            Product::create($data);
            session()->flash('success', 'Product created successfully.');
            return redirect()->route('admin.products.index');
        }
    }

    public function render()
    {
        return view('livewire.product-form');
    }
}
