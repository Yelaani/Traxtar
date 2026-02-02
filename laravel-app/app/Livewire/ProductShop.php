<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ProductShop extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'latest'; // latest, price_low, price_high, name
    public $category = 'all'; // all, women, men, accessories, gifts

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'latest'],
        'category' => ['except' => 'all'],
    ];


    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Only show active products on the shop page
        $query = Product::query()->active();

        // Apply category filter
        if ($this->category !== 'all') {
            if ($this->category === 'women') {
                // Match products that start with "Women's" (case-insensitive)
                // Since "Women's" contains "men's" as a substring, we need to check the start of the name
                $query->whereRaw("LOWER(name) LIKE ?", ["women's%"]);
            } elseif ($this->category === 'men') {
                // Match products that start with "Men's" (case-insensitive)
                // Since "Women's" contains "men's" as a substring, we need to check the start of the name
                $query->whereRaw("LOWER(name) LIKE ?", ["men's%"]);
            } elseif ($this->category === 'accessories') {
                // Accessories: everything that doesn't start with "Women's" or "Men's"
                $query->whereRaw("LOWER(name) NOT LIKE ?", ["women's%"])
                      ->whereRaw("LOWER(name) NOT LIKE ?", ["men's%"]);
            } elseif ($this->category === 'gifts') {
                // Gifts category - products with "E-Gift Card" in the name
                $query->whereRaw("LOWER(name) LIKE ?", ["%e-gift card%"]);
            }
        }

        // Apply search
        if ($this->search) {
            $query->search($this->search);
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12);

        return view('livewire.product-shop', [
            'products' => $products,
        ]);
    }
}
