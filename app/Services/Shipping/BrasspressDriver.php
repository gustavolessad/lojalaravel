<?php

namespace App\Services\Shipping;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrasspressDriver implements ShippingDriverInterface
{
    private const API_URL = 'https://api.braspress.com/v1/cotacao/calcular/json';

    public function isConfigured(): bool
    {
        return (bool) Setting::get('shipping_braspress_active', false)
            && trim((string) Setting::get('shipping_braspress_cnpj', '')) !== ''
            && trim((string) Setting::get('shipping_braspress_usuario', '')) !== ''
            && trim((string) Setting::get('shipping_braspress_senha', '')) !== '';
    }

    public function getLabel(): string
    {
        return 'Braspress';
    }

    /**
     * Retorna as opções de frete da Braspress.
     *
     * @return array<int, array{name: string, price: float, days: int, company: string}>
     */
    public function quote(ShippingContext $context): array
    {
        $cnpj      = preg_replace('/\D/', '', (string) Setting::get('shipping_braspress_cnpj', ''));
        $usuario   = trim((string) Setting::get('shipping_braspress_usuario', ''));
        $senha     = trim((string) Setting::get('shipping_braspress_senha', ''));
        $extraDays = (int) Setting::get('shipping_additional_days', 0)
                   + (int) Setting::get('shipping_braspress_additional_days', 0);

        if (! $cnpj || ! $usuario || ! $senha) {
            return [];
        }

        $cacheKey = 'braspress_' . md5("{$context->originZip}_{$context->destZip}_{$context->totalWeight}_{$context->subtotal}");

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = $this->fetchFromApi($cnpj, $usuario, $senha, $extraDays, $context);

        if (! empty($result)) {
            Cache::put($cacheKey, $result, 1800);
        }

        return $result;
    }

    private function fetchFromApi(
        string          $cnpj,
        string          $usuario,
        string          $senha,
        int             $extraDays,
        ShippingContext  $context
    ): array {

        $totalVolumes = 0;
        $cubagem = [];

        if ($context->cartItems?->isNotEmpty()) {
            foreach ($context->cartItems as $item) {
                $product = $item->product;
                $qty     = (int) $item->quantity;

                $totalVolumes += $qty;

                $cubagem[] = [
                    'altura'      => (float) (($product?->height ?? 5) / 100),
                    'largura'     => (float) (($product?->width ?? 15) / 100),
                    'comprimento' => (float) (($product?->length ?? 20) / 100),
                    'volumes'     => $qty,
                ];
            }
        }

        if ($totalVolumes === 0) {
            $totalVolumes = 1;
            $cubagem[] = [
                'altura'      => 0.05,
                'largura'     => 0.15,
                'comprimento' => 0.20,
                'volumes'     => 1,
            ];
        }

        $payload = [
            'cnpjRemetente'    => (float) $cnpj,
            'cnpjDestinatario' => (float) $cnpj,
            'modal'            => 'R',
            'tipoFrete'        => '1',
            'cepOrigem'        => (int) $context->originZip,
            'cepDestino'       => (int) $context->destZip,
            'vlrMercadoria'    => (float) $context->subtotal,
            'peso'             => max(0.1, (float) $context->totalWeight),
            'volumes'          => $totalVolumes,
            'cubagem'          => $cubagem,
        ];

        try {
            $response = Http::withBasicAuth($usuario, $senha)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->timeout(10)
                ->post(self::API_URL, $payload);

            if (! $response->successful()) {
                Log::warning('Braspress API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            return $this->parseResponse($response->json(), $extraDays);

        } catch (\Throwable $e) {
            Log::error('Braspress request failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function parseResponse(array $data, int $extraDays): array
    {
        $valor = (float) ($data['totalFrete'] ?? $data['vlrFrete'] ?? 0);

        if ($valor <= 0) {
            return [];
        }

        $prazo = (int) ($data['prazoEntrega'] ?? $data['prazoEntregue'] ?? 5);

        return [[
            'name'    => 'Rodoviário',
            'company' => 'Braspress',
            'price'   => $valor,
            'days'    => $prazo + $extraDays,
        ]];
    }
}
