<?php

namespace App\Livewire\Shop;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartIcon extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('cart-updated')]
    public function refreshCount(): void
    {
        $this->count = app(CartService::class)->count();
    }

    public function render()
    {
        return view('livewire.shop.cart-icon');
    }
}
