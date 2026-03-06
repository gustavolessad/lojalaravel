<?php

namespace App\Services\Shipping;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MelhorEnvioDriver implements ShippingDriverInterface
{
    private const PROD_URL    = 'https://www.melhorenvio.com.br/api/v2/me/shipment/calculate';
    private const SANDBOX_URL = 'https://sandbox.melhorenvio.com.br/api/v2/me/shipment/calculate';

    public function isConfigured(): bool
    {
        return (bool) Setting::get('shipping_melhorenvio_active', false)
            && trim((string) Setting::get('shipping_token', '')) !== ''
            && strlen(preg_replace('/\D/', '', (string) Setting::get('shipping_origin_cep', ''))) === 8;
    }

    public function getLabel(): string
    {
        return 'Melhor Envio';
    }

    /**
     * Retorna as opções de frete do Melhor Envio.
     *
     * @return array<int, array{name: string, price: float, days: int, company: string, service_id: int|null}>
     */
    public function quote(ShippingContext $context): array
    {
        $token     = trim((string) Setting::get('shipping_token', ''));
        $sandbox   = (bool) Setting::get('shipping_sandbox', true);
        $extraDays = (int) Setting::get('shipping_additional_days', 0);

        if (! $token || ! $context->cartItems?->isNotEmpty()) {
            return [];
        }

        $itemsKey = $context->cartItems->map(fn ($i) => "{$i->id}:{$i->quantity}")->implode(',');
        $cacheKey = 'melhorenvio_' . md5("{$context->originZip}_{$context->destZip}_{$itemsKey}");

        // Só usa cache se já houver resultado válido — erros nunca são cacheados
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = $this->fetchFromApi($token, $sandbox, $extraDays, $context->originZip, $context->destZip, $context->cartItems);

        if (! empty($result)) {
            Cache::put($cacheKey, $result, 1800);
        }

        return $result;
    }

    private function fetchFromApi(
        string     $token,
        bool       $sandbox,
        int        $extraDays,
        string     $originZip,
        string     $destZip,
        Collection $cartItems
    ): array {
        $url = $sandbox ? self::SANDBOX_URL : self::PROD_URL;

        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Accept'     => 'application/json',
                    'User-Agent' => 'Loja Virtual (contato@' . parse_url(config('app.url'), PHP_URL_HOST) . ')',
                ])
                ->timeout(10)
                ->post($url, [
                    'from'     => ['postal_code' => $originZip],
                    'to'       => ['postal_code' => $destZip],
                    'products' => $this->buildProducts($cartItems),
                    'options'  => [
                        'receipt'  => false,
                        'own_hand' => false,
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('MelhorEnvio API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            return $this->parseResponse($response->json(), $extraDays);

        } catch (\Throwable $e) {
            Log::error('MelhorEnvio request failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function buildProducts(Collection $cartItems): array
    {
        return $cartItems->map(function ($item) {
            $product = $item->product;

            return [
                'id'              => (string) ($product?->id ?? $item->id),
                'width'           => max(11, (int) round((float) ($product?->width  ?? 15))),
                'height'          => max(2,  (int) round((float) ($product?->height ?? 5))),
                'length'          => max(16, (int) round((float) ($product?->length ?? 20))),
                'weight'          => max(0.1, (float) ($product?->weight ?? 0.3)),
                'insurance_value' => (float) $item->unit_price,
                'quantity'        => $item->quantity,
            ];
        })->values()->all();
    }

    private function parseResponse(array $data, int $extraDays): array
    {
        // Serviços permitidos nas configurações (vazio = exibe todos)
        $allowedServices = array_filter(
            array_map('intval', explode(',', (string) Setting::get('shipping_services', '')))
        );

        $options = [];

        foreach ($data as $service) {
            if (! empty($service['error'])) {
                continue;
            }

            if (! empty($allowedServices) && ! in_array((int) ($service['id'] ?? 0), $allowedServices, true)) {
                continue;
            }

            $price = (float) ($service['custom_price'] ?? $service['price'] ?? 0);
            $days  = (int) ($service['custom_delivery_time'] ?? $service['delivery_time'] ?? 0);

            if ($price <= 0 || $days <= 0) {
                continue;
            }

            $options[] = [
                'name'       => $service['name'] ?? 'Entrega',
                'company'    => $service['company']['name'] ?? '',
                'price'      => $price,
                'days'       => $days + $extraDays,
                'service_id' => $service['id'] ?? null,
            ];
        }

        usort($options, fn ($a, $b) => $a['price'] <=> $b['price']);

        return $options;
    }
}
