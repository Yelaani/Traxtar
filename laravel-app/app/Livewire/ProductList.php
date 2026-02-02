<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductList extends Component
{
    public $search = '';
    public $category = 'all'; // all, women, men, accessories, gifts
    public $statusFilter = 'all'; // all, active, hidden

    protected $listeners = ['product-deleted' => '$refresh'];

    public function deleteProduct($productId)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            session()->flash('error', 'Product not found.');
            return;
        }

        // Soft delete the product (image is kept for potential restore)
        $product->delete();
        
        session()->flash('success', 'Product deleted successfully.');
        $this->dispatch('product-deleted');
    }

    public function render()
    {
        $query = Product::query();

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->status($this->statusFilter);
        }

        // Apply category filter
        if ($this->category !== 'all') {
            if ($this->category === 'women') {
                $query->whereRaw("LOWER(name) LIKE ?", ["women's%"]);
            } elseif ($this->category === 'men') {
                $query->whereRaw("LOWER(name) LIKE ?", ["men's%"]);
            } elseif ($this->category === 'accessories') {
                $query->whereRaw("LOWER(name) NOT LIKE ?", ["women's%"])
                      ->whereRaw("LOWER(name) NOT LIKE ?", ["men's%"]);
            } elseif ($this->category === 'gifts') {
                $query->whereRaw("LOWER(name) LIKE ?", ["%e-gift card%"]);
            }
        }

        // Apply search
        if ($this->search) {
            $query->search($this->search);
        }

        // Paginate results (10 per page)
        $products = $query->latest()->paginate(10);

        return view('livewire.product-list', [
            'products' => $products,
        ]);
    }
}
