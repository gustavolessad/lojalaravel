<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Retorna o carrinho atual (do cliente logado ou da sessão anônima).
     */
    public function current(): Cart
    {
        if (Auth::guard('customer')->check()) {
            return $this->cartForCustomer(Auth::guard('customer')->id());
        }

        return $this->cartForSession();
    }

    /**
     * Adiciona um produto ao carrinho.
     * Se já existe, incrementa a quantidade.
     */
    public function add(int $productId, int $quantity = 1, ?int $variantId = null): CartItem
    {
        $cart = $this->current();

        $product = Product::findOrFail($productId);
        $variant = $variantId ? ProductVariant::findOrFail($variantId) : null;

        $unitPrice = $variant
            ? $variant->getEffectivePrice()
            : $product->getCurrentPrice();

        $item = $cart->items()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $item = $cart->items()->create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity'   => $quantity,
                'unit_price' => $unitPrice,
            ]);
        }

        return $item->fresh();
    }

    /**
     * Atualiza a quantidade de um item.
     * Se quantity <= 0, remove o item.
     */
    public function update(int $cartItemId, int $quantity): void
    {
        $cart = $this->current();
        $item = $cart->items()->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }
    }

    /**
     * Remove um item do carrinho.
     */
    public function remove(int $cartItemId): void
    {
        $cart = $this->current();
        $cart->items()->where('id', $cartItemId)->delete();
    }

    /**
     * Esvazia o carrinho.
     */
    public function clear(): void
    {
        $cart = $this->current();
        $cart->items()->delete();
        $cart->update(['coupon_code' => null, 'coupon_discount' => 0]);
    }

    /**
     * Funde o carrinho anônimo da sessão com o carrinho do cliente logado.
     * Chamado após o login.
     */
    public function mergeSessionIntoCustomer(int $customerId): void
    {
        $sessionId = Session::get('cart_session_id');

        if (! $sessionId) {
            return;
        }

        $sessionCart = Cart::where('session_id', $sessionId)
            ->whereNull('customer_id')
            ->first();

        if (! $sessionCart || $sessionCart->items()->count() === 0) {
            return;
        }

        $customerCart = $this->cartForCustomer($customerId);

        foreach ($sessionCart->items as $item) {
            $existing = $customerCart->items()
                ->where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                $customerCart->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                ]);
            }
        }

        $sessionCart->delete();
        Session::forget('cart_session_id');
    }

    /**
     * Retorna a contagem de itens do carrinho atual (para exibir no ícone).
     */
    public function count(): int
    {
        return $this->current()->items()->sum('quantity');
    }

    // ──────────────────────────────────────────────────────────────

    private function cartForCustomer(int $customerId): Cart
    {
        return Cart::firstOrCreate(['customer_id' => $customerId]);
    }

    private function cartForSession(): Cart
    {
        // Tenta recuperar da sessão primeiro; se a sessão expirou, usa o cookie
        $sessionId = Session::get('cart_session_id')
            ?? request()->cookie('cart_id');

        if (! $sessionId) {
            $sessionId = (string) Str::uuid();
        }

        // Persiste na sessão e num cookie de 30 dias para sobreviver a expiração de sessão
        Session::put('cart_session_id', $sessionId);
        Cookie::queue('cart_id', $sessionId, 60 * 24 * 30);

        return Cart::firstOrCreate(
            ['session_id' => $sessionId, 'customer_id' => null]
        );
    }
}
