<?php

namespace App\Services;

use App\Models\FreeShippingRule;
use App\Models\Product;
use App\Models\Setting;
use App\Services\Shipping\ManualCarrierDriver;
use App\Services\Shipping\MelhorEnvioDriver;
use Illuminate\Support\Collection;

/**
 * Cálculo de frete.
 * Prioridade: MelhorEnvio + Manual → fallback tabela fixa por faixa de CEP.
 * Frete grátis: controlado exclusivamente pelas regras do admin (Logística > Frete Grátis).
 */
class ShippingCalculator
{

    /**
     * Retorna as opções de frete disponíveis para o CEP informado.
     *
     * @param  string          $cep       CEP somente dígitos (8 chars) ou com hífen
     * @param  float           $subtotal  Subtotal do carrinho (para frete grátis)
     * @param  Collection|null $cartItems Itens do carrinho (com relação product carregada)
     * @return array<int, array{name: string, price: float, days: int}>
     */
    public function calculate(string $cep, float $subtotal, ?Collection $cartItems = null): array
    {
        $digits      = preg_replace('/\D/', '', $cep);
        $token       = Setting::get('shipping_token');
        $originZip   = preg_replace('/\D/', '', (string) Setting::get('shipping_origin_cep', ''));
        $totalWeight = $this->calcTotalWeight($cartItems);

        $options = [];

        // 1. Melhor Envio — quando token e CEP de origem estão configurados
        if ($token && strlen($originZip) === 8 && $cartItems?->isNotEmpty()) {
            $meOptions = app(MelhorEnvioDriver::class)->quote($originZip, $digits, $cartItems);
            $options   = array_merge($options, $meOptions);
        }

        // 2. Transportadoras manuais (Hub de Frete) — filtradas por CEP e peso
        $manualOptions = app(ManualCarrierDriver::class)->quote($digits, $totalWeight);
        $options       = array_merge($options, $manualOptions);

        // Se obteve alguma opção, retorna mesclado e ordenado por preço
        if (! empty($options)) {
            usort($options, fn ($a, $b) => $a['price'] <=> $b['price']);
            return $this->applyFreeShipping($options, $subtotal, $digits, $cartItems);
        }

        // 3. Fallback: tabela fixa por faixa de CEP
        $prefix  = (int) substr($digits, 0, 5);
        $options = $this->optionsForPrefix($prefix);

        return $this->applyFreeShipping($options, $subtotal, $digits, $cartItems);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /** Soma o peso total dos itens do carrinho em kg. */
    private function calcTotalWeight(?Collection $cartItems): float
    {
        if (! $cartItems || $cartItems->isEmpty()) {
            return 0.0;
        }

        return $cartItems->sum(
            fn ($item) => (float) ($item->product?->weight ?? 0.3) * $item->quantity
        );
    }

    /**
     * Verifica regras de frete grátis do admin e, se houver correspondência,
     * injeta a opção "Frete Grátis" no início da lista.
     */
    private function applyFreeShipping(
        array $options,
        float $subtotal,
        string $cep,
        ?Collection $cartItems
    ): array {
        $freeRule = $this->matchingFreeShippingRule($subtotal, $cep, $cartItems);

        if ($freeRule !== null) {
            array_unshift($options, [
                'name'    => 'Frete Grátis',
                'company' => '',
                'price'   => 0.0,
                'days'    => $options[0]['days'] ?? 5,
            ]);
        }

        return $options;
    }

    /**
     * Retorna a primeira regra de frete grátis que se aplica ao pedido, ou null.
     */
    private function matchingFreeShippingRule(
        float $subtotal,
        string $cep,
        ?Collection $cartItems
    ): ?FreeShippingRule {
        $rules = FreeShippingRule::active()
            ->where(function ($q) use ($cep) {
                $q->whereNull('cep_from')->orWhere('cep_from', '<=', $cep);
            })
            ->where(function ($q) use ($cep) {
                $q->whereNull('cep_to')->orWhere('cep_to', '>=', $cep);
            })
            ->where(function ($q) use ($subtotal) {
                $q->whereNull('min_cart_value')->orWhere('min_cart_value', '<=', $subtotal);
            })
            ->with('conditions')
            ->get();

        foreach ($rules as $rule) {
            if ($this->ruleAppliesToCart($rule, $cartItems)) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * Verifica se as condições de inclusão/exclusão da regra permitem frete grátis.
     */
    private function ruleAppliesToCart(FreeShippingRule $rule, ?Collection $cartItems): bool
    {
        if ($rule->conditions->isEmpty()) {
            return true; // sem condições = aplica para todos
        }

        if (! $cartItems || $cartItems->isEmpty()) {
            return true;
        }

        $cartProductIds = $cartItems->pluck('product_id')->filter()->unique()->values();

        // Carrega categorias dos produtos do carrinho em uma única query
        $cartCategoryIds = Product::whereIn('id', $cartProductIds)
            ->with('categories:id')
            ->get()
            ->flatMap(fn ($p) => $p->categories->pluck('id'))
            ->unique();

        foreach ($rule->conditions as $condition) {
            switch ($condition->type) {
                case 'exclude_category':
                    if ($cartCategoryIds->contains($condition->item_id)) {
                        return false; // categoria excluída está no carrinho
                    }
                    break;

                case 'exclude_product':
                    if ($cartProductIds->contains($condition->item_id)) {
                        return false; // produto excluído está no carrinho
                    }
                    break;

                case 'include_category':
                    if (! $cartCategoryIds->contains($condition->item_id)) {
                        return false; // categoria obrigatória não está no carrinho
                    }
                    break;

                case 'include_product':
                    if (! $cartProductIds->contains($condition->item_id)) {
                        return false; // produto obrigatório não está no carrinho
                    }
                    break;
            }
        }

        return true;
    }

    private function optionsForPrefix(int $prefix): array
    {
        return match (true) {
            // SP capital: 01000–09999
            $prefix >= 1000 && $prefix <= 9999 => [
                ['name' => 'PAC',   'price' => 14.90, 'days' => 8],
                ['name' => 'Sedex', 'price' => 27.90, 'days' => 2],
            ],
            // SP interior: 10000–19999
            $prefix >= 10000 && $prefix <= 19999 => [
                ['name' => 'PAC',   'price' => 16.90, 'days' => 10],
                ['name' => 'Sedex', 'price' => 29.90, 'days' => 3],
            ],
            // RJ + ES: 20000–29999
            $prefix >= 20000 && $prefix <= 29999 => [
                ['name' => 'PAC',   'price' => 18.90, 'days' => 9],
                ['name' => 'Sedex', 'price' => 31.90, 'days' => 3],
            ],
            // MG: 30000–39999
            $prefix >= 30000 && $prefix <= 39999 => [
                ['name' => 'PAC',   'price' => 17.90, 'days' => 9],
                ['name' => 'Sedex', 'price' => 30.90, 'days' => 3],
            ],
            // Nordeste: 40000–65999
            $prefix >= 40000 && $prefix <= 65999 => [
                ['name' => 'PAC',   'price' => 22.90, 'days' => 12],
                ['name' => 'Sedex', 'price' => 39.90, 'days' => 5],
            ],
            // Norte: 66000–69999
            $prefix >= 66000 && $prefix <= 69999 => [
                ['name' => 'PAC',   'price' => 25.90, 'days' => 15],
                ['name' => 'Sedex', 'price' => 44.90, 'days' => 6],
            ],
            // Centro-Oeste + DF: 70000–79999
            $prefix >= 70000 && $prefix <= 79999 => [
                ['name' => 'PAC',   'price' => 20.90, 'days' => 11],
                ['name' => 'Sedex', 'price' => 36.90, 'days' => 4],
            ],
            // Sul: 80000–99999
            $prefix >= 80000 && $prefix <= 99999 => [
                ['name' => 'PAC',   'price' => 19.90, 'days' => 10],
                ['name' => 'Sedex', 'price' => 33.90, 'days' => 3],
            ],
            // Fallback
            default => [
                ['name' => 'PAC',   'price' => 24.90, 'days' => 14],
                ['name' => 'Sedex', 'price' => 42.90, 'days' => 6],
            ],
        };
    }
}
