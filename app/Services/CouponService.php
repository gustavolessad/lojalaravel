<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;

class CouponService
{
    /**
     * Valida um código de cupom e retorna o cupom + desconto calculado, ou uma string de erro.
     *
     * @return array{coupon: Coupon, discount: float}|string
     */
    public function validate(
        string    $code,
        float     $subtotal,
        ?Customer $customer,
        ?Collection $cartItems = null
    ): array|string {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            return 'Cupom não encontrado.';
        }

        if (! $coupon->active) {
            return 'Este cupom está inativo.';
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            return 'Este cupom ainda não está válido.';
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            return 'Este cupom está expirado.';
        }

        if ($coupon->min_cart_value && $subtotal < $coupon->min_cart_value) {
            return 'O valor mínimo do pedido para este cupom é ' . 'R$ ' . number_format($coupon->min_cart_value, 2, ',', '.');
        }

        if ($coupon->max_uses !== null && $coupon->usages()->count() >= $coupon->max_uses) {
            return 'Este cupom atingiu o limite de usos.';
        }

        if ($customer && $coupon->max_uses_per_customer !== null) {
            $usedByCustomer = $coupon->usages()->where('customer_id', $customer->id)->count();
            if ($usedByCustomer >= $coupon->max_uses_per_customer) {
                return 'Você já utilizou este cupom o número máximo de vezes.';
            }
        }

        // Verifica condições de inclusão/exclusão
        $conditionError = $this->checkConditions($coupon, $cartItems);
        if ($conditionError) {
            return $conditionError;
        }

        $discount = $this->calculateDiscount($coupon, $subtotal);

        return ['coupon' => $coupon, 'discount' => $discount];
    }

    /**
     * Calcula o valor do desconto para um subtotal de carrinho.
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->type === 'percent') {
            $discount = $subtotal * ($coupon->value / 100);
        } else {
            $discount = $coupon->value;
        }

        // Desconto não pode exceder o subtotal
        return min(round($discount, 2), $subtotal);
    }

    /**
     * Registra o uso do cupom após a criação do pedido.
     */
    public function recordUsage(Coupon $coupon, Order $order, ?Customer $customer, float $discountAmount): void
    {
        CouponUsage::create([
            'coupon_id'       => $coupon->id,
            'order_id'        => $order->id,
            'customer_id'     => $customer?->id,
            'discount_amount' => $discountAmount,
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function checkConditions(Coupon $coupon, ?Collection $cartItems): ?string
    {
        $conditions = $coupon->conditions()->get();

        if ($conditions->isEmpty() || ! $cartItems || $cartItems->isEmpty()) {
            return null;
        }

        $cartProductIds  = $cartItems->pluck('product_id')->filter()->unique()->values();
        $cartCategoryIds = Product::whereIn('id', $cartProductIds)
            ->with('categories:id')
            ->get()
            ->flatMap(fn ($p) => $p->categories->pluck('id'))
            ->unique();

        foreach ($conditions as $condition) {
            switch ($condition->type) {
                case 'exclude_category':
                    if ($cartCategoryIds->contains($condition->item_id)) {
                        return 'Este cupom não é válido para produtos da categoria selecionada.';
                    }
                    break;

                case 'exclude_product':
                    if ($cartProductIds->contains($condition->item_id)) {
                        return 'Este cupom não é válido para um ou mais produtos do seu carrinho.';
                    }
                    break;

                case 'include_category':
                    if (! $cartCategoryIds->contains($condition->item_id)) {
                        return 'Este cupom é válido apenas para produtos de categorias específicas.';
                    }
                    break;

                case 'include_product':
                    if (! $cartProductIds->contains($condition->item_id)) {
                        return 'Este cupom é válido apenas para produtos específicos.';
                    }
                    break;
            }
        }

        return null;
    }
}
