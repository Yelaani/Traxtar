<?php

namespace App\Livewire;

use Livewire\Component;

class CartCounter extends Component
{
    public $count = 0;

    protected $listeners = ['cart-updated' => 'updateCount'];

    public function mount()
    {
        $this->updateCount();
    }

    public function updateCount()
    {
        $cart = session()->get('cart', []);
        $this->count = 0;
        
        foreach ($cart as $item) {
            $this->count += $item['qty'] ?? 0;
        }
    }

    public function render()
    {
        return view('livewire.cart-counter');
    }
}
