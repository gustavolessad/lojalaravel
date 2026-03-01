<?php

namespace App\Livewire\Shop;

use App\Models\Cart;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\ShippingCalculator;
use Livewire\Component;

class CartPage extends Component
{
    public ?Cart $cart = null;

    /** Nomes dos itens removidos automaticamente por falta de estoque */
    public array $removedItems = [];

    // Frete
    public string $cep = '';
    public ?array $shipping = null;
    public ?string $shippingError = null;
    public ?string $shippingCity = null;

    // Cupom
    public string $couponCode = '';
    public ?string $couponError = null;
    public ?string $couponSuccess = null;

    public function mount(): void
    {
        $this->loadCart();
        $this->couponCode = $this->cart?->coupon_code ?? '';
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $cartService = app(CartService::class);
        $cart        = $cartService->current();
        $item        = $cart->items()->with(['product', 'variant'])->findOrFail($itemId);

        $maxStock = $item->variant
            ? (int) ($item->variant->stock ?? 0)
            : (int) ($item->product->stock ?? 0);

        $safeQty = min(max(1, $quantity), max(1, $maxStock));

        $cartService->update($itemId, $safeQty);
        $this->revalidateCoupon();
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function removeItem(int $itemId): void
    {
        app(CartService::class)->remove($itemId);
        $this->revalidateCoupon();
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function clearCart(): void
    {
        app(CartService::class)->clear();
        $this->removeCoupon();
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function dismissRemovedNotice(): void
    {
        $this->removedItems = [];
    }

    public function simulateShipping(): void
    {
        $this->validate(['cep' => 'required|regex:/^\d{5}-?\d{3}$/']);

        $cep                 = preg_replace('/\D/', '', $this->cep);
        $this->shippingError = null;
        $subtotal            = (float) ($this->cart?->subtotal ?? 0);
        $items               = $this->cart?->items ?? collect();

        $options = app(ShippingCalculator::class)->calculate($cep, $subtotal, $items);

        if (empty($options)) {
            $this->shippingError = 'Não foi possível calcular o frete para este CEP.';
            $this->shipping      = null;
            return;
        }

        $this->shipping = $options;

        // Busca nome da cidade via ViaCEP para exibir no título do accordion
        try {
            $viaCep = \Illuminate\Support\Facades\Http::timeout(3)
                ->get("https://viacep.com.br/ws/{$cep}/json/");

            if ($viaCep->successful() && ! $viaCep->json('erro')) {
                $data = $viaCep->json();
                $this->shippingCity = trim(($data['localidade'] ?? '') . ' — ' . ($data['uf'] ?? ''));
            }
        } catch (\Throwable) {
            // ViaCEP indisponível — não bloqueia o cálculo de frete
        }
    }

    // ── Cupom ─────────────────────────────────────────────────────────────

    public function applyCoupon(): void
    {
        $this->couponError   = null;
        $this->couponSuccess = null;

        if (blank($this->couponCode)) {
            $this->couponError = 'Digite um código de cupom.';
            return;
        }

        $cart     = $this->cart;
        $subtotal = (float) ($cart?->subtotal ?? 0);
        $items    = $cart?->items ?? collect();
        $customer = auth('customer')->user();

        $result = app(CouponService::class)->validate(
            $this->couponCode,
            $subtotal,
            $customer,
            $items
        );

        if (is_string($result)) {
            $this->couponError = $result;
            return;
        }

        // Persiste no carrinho
        $cart->update([
            'coupon_code'     => $result['coupon']->code,
            'coupon_discount' => $result['discount'],
        ]);

        $this->loadCart();
        $this->couponCode    = $result['coupon']->code;
        $this->couponSuccess = 'Cupom aplicado! Desconto de R$ ' . number_format($result['discount'], 2, ',', '.');
        $this->dispatch('cart-updated');
    }

    public function removeCoupon(): void
    {
        $this->cart?->update(['coupon_code' => null, 'coupon_discount' => 0]);
        $this->couponCode    = '';
        $this->couponError   = null;
        $this->couponSuccess = null;
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    // ── Private ───────────────────────────────────────────────────────────

    /** Re-valida o cupom atual ao alterar itens (subtotal pode mudar). */
    private function revalidateCoupon(): void
    {
        if (! $this->cart?->coupon_code) {
            return;
        }

        $cart     = app(CartService::class)->current()->load('items.product');
        $subtotal = (float) $cart->subtotal;
        $items    = $cart->items;
        $customer = auth('customer')->user();

        $result = app(CouponService::class)->validate(
            $this->cart->coupon_code,
            $subtotal,
            $customer,
            $items
        );

        if (is_string($result)) {
            // Cupom ficou inválido — remove silenciosamente
            $cart->update(['coupon_code' => null, 'coupon_discount' => 0]);
        } else {
            // Recalcula desconto
            $cart->update(['coupon_discount' => $result['discount']]);
        }
    }

    private function loadCart(): void
    {
        $cartService = app(CartService::class);

        $this->cart = $cartService
            ->current()
            ->load(['items.product', 'items.variant.attributeValues.attribute']);

        $removed      = [];
        $priceChanged = false;

        foreach ($this->cart->items as $item) {
            $stock = $item->variant
                ? (int) ($item->variant->stock ?? 0)
                : (int) ($item->product->stock ?? 0);

            if ($stock <= 0) {
                $label = $item->product->name;
                if ($item->variant?->label) {
                    $label .= ' — ' . $item->variant->label;
                }
                $removed[] = $label;
                $cartService->remove($item->id);
                continue;
            }

            // Sincroniza unit_price com o preço efetivo atual do produto/variante
            $currentPrice = $item->variant
                ? $item->variant->getEffectivePrice()
                : $item->product->getCurrentPrice();

            if ((float) $item->unit_price !== $currentPrice) {
                $item->update(['unit_price' => $currentPrice]);
                $priceChanged = true;
            }
        }

        if (! empty($removed) || $priceChanged) {
            $this->removedItems = array_merge($this->removedItems, $removed);

            $this->cart = $cartService
                ->current()
                ->load(['items.product', 'items.variant.attributeValues.attribute']);

            $this->dispatch('cart-updated');
        }
    }

    public function render()
    {
        return view('livewire.shop.cart-page');
    }
}
