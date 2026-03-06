<?php

namespace App\Services\Shipping;

use App\Models\Shipping\Carrier;

class ManualCarrierDriver implements ShippingDriverInterface
{
    public function isConfigured(): bool
    {
        return Carrier::active()->exists();
    }

    public function getLabel(): string
    {
        return 'Hub de Frete';
    }

    /**
     * Retorna as opções de frete cadastradas manualmente para o CEP e peso informados.
     *
     * @return array<int, array{name: string, company: string, price: float, days: int, service_id: null}>
     */
    public function quote(ShippingContext $context): array
    {
        $carriers = Carrier::active()->with('rates')->get();

        $options = [];

        foreach ($carriers as $carrier) {
            $rate = $carrier->rateForCepAndWeight($context->destZip, $context->totalWeight);

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
