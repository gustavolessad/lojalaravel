<?php

namespace App\Livewire\Shop;

use App\Services\Cart\CartService;
use App\Services\Payment\PaymentCalculator;
use Illuminate\Support\Collection;
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

    public function removeItem(int $itemId): void
    {
        app(CartService::class)->remove($itemId);
        $this->refreshCount();
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $cart  = app(CartService::class)->current()->load([
            'items.product.media',
            'items.variant.attributeValues.attribute',
            'items.variant.variantGroup.media',
        ]);

        $items    = $cart->items->sortByDesc('created_at')->take(5);
        $subtotal = (float) $cart->subtotal;
        $calc     = app(PaymentCalculator::class);

        return view('livewire.shop.cart-icon', [
            'items'      => $items,
            'subtotal'   => $subtotal,
            'pixPrice'   => $calc->pixPrice($subtotal),
            'instLabel'  => $calc->bestFreeInstallmentLabel($subtotal),
            'totalItems' => $cart->items->count(),
        ]);
    }
}
