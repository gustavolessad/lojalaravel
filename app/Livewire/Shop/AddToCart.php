<?php

namespace App\Livewire\Shop;

use App\Services\CartService;
use Livewire\Component;

class AddToCart extends Component
{
    public int $productId;
    public ?int $variantId = null;
    public int $quantity = 1;
    public bool $added = false;

    public function add(): void
    {
        $this->validate(['quantity' => 'required|integer|min:1|max:999']);

        app(CartService::class)->add($this->productId, $this->quantity, $this->variantId);

        $this->added = true;
        $this->dispatch('cart-updated');

        // Reset feedback após 2s via JS
        $this->js("setTimeout(() => \$wire.added = false, 2000)");
    }

    public function render()
    {
        return view('livewire.shop.add-to-cart');
    }
}
