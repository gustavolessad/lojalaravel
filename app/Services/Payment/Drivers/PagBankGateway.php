<?php

namespace App\Services\Payment\Drivers;

use App\Models\Setting;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentPayload;
use App\Services\Payment\PaymentResult;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagBankGateway implements PaymentGatewayInterface
{
    private string $baseUrl;
    private string $token;
    private ?array $lastError = null;

    public function __construct()
    {
        $sandbox     = (bool) Setting::get('payment_pagbank_sandbox', true);
        $this->token = (string) Setting::get('payment_pagbank_token', '');
        $this->baseUrl = $sandbox
            ? 'https://sandbox.api.pagseguro.com'
            : 'https://api.pagseguro.com';
    }

    // ── Interface ─────────────────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return $this->token !== '';
    }

    public function createCharge(PaymentPayload $payload): PaymentResult
    {
        return match ($payload->method) {
            'pix'         => $this->doPixCharge($payload),
            'credit_card' => $this->doCardCharge($payload),
            'boleto'      => $this->doBoletoCharge($payload),
            default       => throw new \InvalidArgumentException("Método de pagamento [{$payload->method}] não suportado pelo PagBank."),
        };
    }

    public function getLastError(): ?array
    {
        return $this->lastError;
    }

    // ── PIX ───────────────────────────────────────────────────────────────

    private function doPixCharge(PaymentPayload $payload): PaymentResult
    {
        $cpfCnpj = preg_replace('/\D/', '', $payload->customerCpfCnpj ?? '');
        $phone   = preg_replace('/\D/', '', $payload->customerPhone ?? '');

        $body = [
            'reference_id'      => $payload->reference,
            'customer'          => [
                'name'   => $payload->customerName,
                'email'  => $payload->customerEmail,
                'tax_id' => $cpfCnpj,
                'phones' => $phone ? [[
                    'country' => '55',
                    'area'    => substr($phone, 0, 2),
                    'number'  => substr($phone, 2),
                    'type'    => 'MOBILE',
                ]] : [],
            ],
            'items' => [[
                'name'        => $payload->description,
                'quantity'    => 1,
                'unit_amount' => $this->toCents($payload->amount),
            ]],
            'qr_codes' => [[
                'amount' => [
                    'value' => $this->toCents($payload->amount),
                ],
                'expiration_date' => now()->addDay()->toIso8601String(),
            ]],
            'notification_urls' => [
                route('webhook.pagbank'),
            ],
        ];

        $response = $this->post('/orders', $body);

        if (! $response->successful()) {
            $this->lastError = $response->json();
            Log::error('PagBankGateway: erro ao criar PIX', [
                'status' => $response->status(),
                'body'   => $this->lastError,
            ]);
            throw new \RuntimeException('Falha ao gerar o QR Code PIX. Tente novamente.');
        }

        $data      = $response->json();
        $orderId   = $data['id'] ?? '';
        $qrCodes   = $data['qr_codes'] ?? [];
        $qrCode    = $qrCodes[0] ?? [];
        $copyPaste = $qrCode['text'] ?? null;

        // Busca link da imagem QR Code e converte para base64
        $qrImage = null;
        foreach ($qrCode['links'] ?? [] as $link) {
            if (($link['rel'] ?? '') === 'QRCODE.PNG') {
                $qrUrl = $link['href'] ?? null;
                if ($qrUrl) {
                    try {
                        $imgContents = Http::timeout(10)->get($qrUrl)->body();
                        $qrImage = base64_encode($imgContents);
                    } catch (\Throwable $e) {
                        Log::warning('PagBankGateway: falha ao baixar imagem QR Code', ['url' => $qrUrl, 'error' => $e->getMessage()]);
                    }
                }
                break;
            }
        }

        return new PaymentResult(
            transactionId: $orderId,
            status:        'PENDING',
            method:        'pix',
            amount:        $payload->amount,
            pixCopyPaste:  $copyPaste,
            pixQrCode:     $qrImage,
            pixExpiresAt:  $qrCode['expiration_date'] ?? null,
        );
    }

    // ── Cartão de crédito ─────────────────────────────────────────────────

    private function doCardCharge(PaymentPayload $payload): PaymentResult
    {
        $cpfCnpj = preg_replace('/\D/', '', $payload->customerCpfCnpj ?? '');
        $phone   = preg_replace('/\D/', '', $payload->customerPhone ?? '');

        $body = [
            'reference_id'      => $payload->reference,
            'customer'          => [
                'name'   => $payload->customerName,
                'email'  => $payload->customerEmail,
                'tax_id' => $cpfCnpj,
                'phones' => $phone ? [[
                    'country' => '55',
                    'area'    => substr($phone, 0, 2),
                    'number'  => substr($phone, 2),
                    'type'    => 'MOBILE',
                ]] : [],
            ],
            'items' => [[
                'name'        => $payload->description,
                'quantity'    => 1,
                'unit_amount' => $this->toCents($payload->amount),
            ]],
            'charges' => [[
                'reference_id' => $payload->reference,
                'description'  => $payload->description,
                'amount'       => [
                    'value'    => $this->toCents($payload->amount),
                    'currency' => 'BRL',
                ],
                'payment_method' => [
                    'type'         => 'CREDIT_CARD',
                    'installments' => $payload->installments,
                    'capture'      => true,
                    'card'         => [
                        'encrypted' => $payload->encryptedCard,
                    ],
                ],
                'notification_urls' => [
                    route('webhook.pagbank'),
                ],
            ]],
            'notification_urls' => [
                route('webhook.pagbank'),
            ],
        ];

        $response = $this->post('/orders', $body);

        if (! $response->successful()) {
            $this->lastError = $response->json();
            Log::error('PagBankGateway: erro ao cobrar cartão', [
                'status' => $response->status(),
                'body'   => $this->lastError,
            ]);
            return new PaymentResult(
                transactionId: '',
                status:        'DECLINED',
                method:        'credit_card',
                amount:        $payload->amount,
            );
        }

        $data    = $response->json();
        $orderId = $data['id'] ?? '';
        $charge  = $data['charges'][0] ?? [];
        $status  = $charge['status'] ?? 'DECLINED';

        // Mapeia status PagBank → nosso padrão
        $mappedStatus = match ($status) {
            'PAID', 'AUTHORIZED' => $status,
            'IN_ANALYSIS'        => 'PENDING',
            default              => 'DECLINED',
        };

        return new PaymentResult(
            transactionId: $orderId,
            status:        $mappedStatus,
            method:        'credit_card',
            amount:        $payload->amount,
        );
    }

    // ── Boleto ──────────────────────────────────────────────────────────

    private function doBoletoCharge(PaymentPayload $payload): PaymentResult
    {
        $cpfCnpj = preg_replace('/\D/', '', $payload->customerCpfCnpj ?? '');
        $phone   = preg_replace('/\D/', '', $payload->customerPhone ?? '');
        $zip     = preg_replace('/\D/', '', $payload->billingPostalCode ?? '');

        $dueDays = max(1, (int) Setting::get('payment_boleto_due_days', 3));
        $dueDate = now()->addDays($dueDays)->format('Y-m-d');

        $body = [
            'reference_id'      => $payload->reference,
            'customer'          => [
                'name'   => $payload->customerName,
                'email'  => $payload->customerEmail,
                'tax_id' => $cpfCnpj,
                'phones' => $phone ? [[
                    'country' => '55',
                    'area'    => substr($phone, 0, 2),
                    'number'  => substr($phone, 2),
                    'type'    => 'MOBILE',
                ]] : [],
            ],
            'items' => [[
                'name'        => $payload->description,
                'quantity'    => 1,
                'unit_amount' => $this->toCents($payload->amount),
            ]],
            'charges' => [[
                'reference_id' => $payload->reference,
                'description'  => $payload->description,
                'amount'       => [
                    'value'    => $this->toCents($payload->amount),
                    'currency' => 'BRL',
                ],
                'payment_method' => [
                    'type'   => 'BOLETO',
                    'boleto' => [
                        'due_date' => $dueDate,
                        'holder'   => [
                            'name'    => $payload->customerName,
                            'tax_id'  => $cpfCnpj,
                            'email'   => $payload->customerEmail,
                            'address' => [
                                'street'      => $payload->billingStreet ?? '',
                                'number'      => $payload->billingAddressNumber ?? '',
                                'locality'    => $payload->billingNeighborhood ?? '',
                                'city'        => $payload->billingCity ?? '',
                                'region'      => $this->stateFullName($payload->billingState ?? ''),
                                'region_code' => $payload->billingState ?? '',
                                'postal_code' => $zip,
                                'country'     => 'BRA',
                            ],
                        ],
                        'instruction_lines' => [
                            'line_1' => 'Pagamento referente ao pedido na loja.',
                            'line_2' => 'Após o vencimento, o pedido será cancelado.',
                        ],
                    ],
                ],
                'notification_urls' => [
                    route('webhook.pagbank'),
                ],
            ]],
            'notification_urls' => [
                route('webhook.pagbank'),
            ],
        ];

        $response = $this->post('/orders', $body);

        if (! $response->successful()) {
            $this->lastError = $response->json();
            Log::error('PagBankGateway: erro ao criar boleto', [
                'status' => $response->status(),
                'body'   => $this->lastError,
            ]);
            throw new \RuntimeException('Falha ao gerar o boleto. Tente novamente.');
        }

        $data    = $response->json();
        $orderId = $data['id'] ?? '';
        $charge  = $data['charges'][0] ?? [];
        $boleto  = $charge['payment_method']['boleto'] ?? [];

        // Busca link do PDF do boleto
        $pdfUrl = null;
        foreach ($charge['links'] ?? [] as $link) {
            if (($link['media'] ?? '') === 'application/pdf') {
                $pdfUrl = $link['href'] ?? null;
                break;
            }
        }

        return new PaymentResult(
            transactionId:       $orderId,
            status:              'PENDING',
            method:              'boleto',
            amount:              $payload->amount,
            boletoDigitableLine: $boleto['formatted_barcode'] ?? null,
            boletoBarcode:       $boleto['barcode'] ?? null,
            boletoDueDate:       $boleto['due_date'] ?? $dueDate,
            invoiceUrl:          $pdfUrl,
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function toCents(float $value): int
    {
        return (int) round($value * 100);
    }

    private function stateFullName(string $uf): string
    {
        return match (strtoupper($uf)) {
            'AC' => 'Acre',            'AL' => 'Alagoas',         'AP' => 'Amapá',
            'AM' => 'Amazonas',        'BA' => 'Bahia',           'CE' => 'Ceará',
            'DF' => 'Distrito Federal','ES' => 'Espírito Santo',  'GO' => 'Goiás',
            'MA' => 'Maranhão',        'MT' => 'Mato Grosso',     'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',    'PA' => 'Pará',            'PB' => 'Paraíba',
            'PR' => 'Paraná',          'PE' => 'Pernambuco',      'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',  'RN' => 'Rio Grande do Norte', 'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',        'RR' => 'Roraima',         'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',       'SE' => 'Sergipe',         'TO' => 'Tocantins',
            default => $uf,
        };
    }

    // ── HTTP ──────────────────────────────────────────────────────────────

    private function post(string $path, array $data): Response
    {
        try {
            return Http::withHeaders($this->headers())
                ->timeout(15)
                ->post("{$this->baseUrl}{$path}", $data);
        } catch (\Throwable $e) {
            Log::error('PagBankGateway: falha de conexão (POST)', ['path' => $path, 'error' => $e->getMessage()]);
            return Http::response(['error' => $e->getMessage()], 503);
        }
    }

    private function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }
}
