<?php

namespace App\Services\Shipping;

use App\Models\Carrier;

class ManualCarrierDriver
{
    /**
     * Retorna as opções de frete cadastradas manualmente para o CEP e peso informados.
     *
     * @param  string $destZip     CEP somente dígitos (8 chars)
     * @param  float  $totalWeight Peso total do pedido em kg
     * @return array<int, array{name: string, price: float, days: int, company: string}>
     */
    public function quote(string $destZip, float $totalWeight = 0): array
    {
        $carriers = Carrier::active()->with('rates')->get();

        $options = [];

        foreach ($carriers as $carrier) {
            $rate = $carrier->rateForCepAndWeight($destZip, $totalWeight);

            if ($rate === null) {
                continue;
            }

            $options[] = [
                'name'       => $carrier->name,
                'company'    => '',
                'price'      => (float) $rate->price,
                'days'       => (int) $rate->days,
                'service_id' => null,
            ];
        }

        usort($options, fn ($a, $b) => $a['price'] <=> $b['price']);

        return $options;
    }
}
