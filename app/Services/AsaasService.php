<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $sandbox      = config('services.asaas.sandbox', true);
        $this->apiKey = config('services.asaas.key', '');
        $this->baseUrl = $sandbox
            ? 'https://sandbox.asaas.com/api/v3'
            : 'https://api.asaas.com/api/v3';
    }

    // ── Cliente Asaas ─────────────────────────────────────────────────────

    /**
     * Encontra ou cria o cliente no Asaas pelo CPF/CNPJ.
     * Retorna o ID do cliente Asaas (ex: "cus_000005113026").
     */
    public function findOrCreateCustomer(Order $order): ?string
    {
        $cpfCnpj = $order->customer?->cpf ?? $order->customer?->cnpj;

        if (! $cpfCnpj) {
            // Guest sem CPF/CNPJ — cria sem documento
            $cpfCnpj = null;
        }

        $name  = $order->buyer_name;
        $email = $order->buyer_email;

        // Tenta buscar por CPF/CNPJ
        if ($cpfCnpj) {
            $response = $this->get('/customers', ['cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj)]);
            $data     = $response->json();

            if (! empty($data['data'][0]['id'])) {
                return $data['data'][0]['id'];
            }
        }

        // Cria novo cliente
        $payload = [
            'name'    => $name,
            'email'   => $email,
            'phone'   => preg_replace('/\D/', '', $order->shipping_phone ?? ''),
        ];

        if ($cpfCnpj) {
            $payload['cpfCnpj'] = preg_replace('/\D/', '', $cpfCnpj);
        }

        $response = $this->post('/customers', $payload);

        if (! $response->successful()) {
            Log::error('Asaas: erro ao criar cliente', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return null;
        }

        return $response->json('id');
    }

    // ── PIX ───────────────────────────────────────────────────────────────

    /**
     * Cria um pagamento PIX no Asaas.
     * Retorna array com: id, pixQrCode, pixCopyPasteCode, expiresAt
     */
    public function createPixPayment(Order $order, string $asaasCustomerId): ?array
    {
        $response = $this->post('/payments', [
            'customer'    => $asaasCustomerId,
            'billingType' => 'PIX',
            'value'       => (float) $order->total,
            'dueDate'     => now()->addDay()->format('Y-m-d'),
            'description' => "Pedido #{$order->order_number}",
            'externalReference' => (string) $order->id,
        ]);

        if (! $response->successful()) {
            Log::error('Asaas: erro ao criar PIX', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return null;
        }

        $payment = $response->json();
        $pixId   = $payment['id'];

        // Busca o QR code
        $qrResponse = $this->get("/payments/{$pixId}/pixQrCode");
        $qr         = $qrResponse->json();

        return [
            'payment_id'        => $pixId,
            'status'            => $payment['status'],
            'pix_qrcode'        => $qr['encodedImage'] ?? null,   // base64 da imagem
            'pix_copy_paste'    => $qr['payload'] ?? null,
            'expires_at'        => $payment['dueDate'] ?? null,
            'invoice_url'       => $payment['invoiceUrl'] ?? null,
        ];
    }

    // ── Cartão de crédito ─────────────────────────────────────────────────

    /**
     * Cria um pagamento com cartão de crédito.
     *
     * @param  array $card  [holderName, number, expiryMonth, expiryYear, ccv]
     * @param  int   $installments  Número de parcelas
     */
    public function createCreditCardPayment(
        Order  $order,
        string $asaasCustomerId,
        array  $card,
        int    $installments = 1,
    ): ?array {
        $holderInfo = [
            'name'              => $card['holderName'],
            'email'             => $order->buyer_email,
            'cpfCnpj'           => preg_replace('/\D/', '', $order->customer?->cpf ?? $order->customer?->cnpj ?? ''),
            'postalCode'        => preg_replace('/\D/', '', $order->shipping_zip),
            'addressNumber'     => $order->shipping_number,
            'phone'             => preg_replace('/\D/', '', $order->shipping_phone ?? ''),
        ];

        $response = $this->post('/payments', [
            'customer'     => $asaasCustomerId,
            'billingType'  => 'CREDIT_CARD',
            'value'        => (float) $order->total,
            'dueDate'      => now()->format('Y-m-d'),
            'description'  => "Pedido #{$order->order_number}",
            'externalReference' => (string) $order->id,
            'installmentCount'  => $installments,
            'installmentValue'  => round((float) $order->total / $installments, 2),
            'creditCard'   => [
                'holderName'  => $card['holderName'],
                'number'      => preg_replace('/\D/', '', $card['number']),
                'expiryMonth' => $card['expiryMonth'],
                'expiryYear'  => $card['expiryYear'],
                'ccv'         => $card['ccv'],
            ],
            'creditCardHolderInfo' => $holderInfo,
        ]);

        if (! $response->successful()) {
            Log::error('Asaas: erro ao cobrar cartão', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return null;
        }

        $payment = $response->json();

        return [
            'payment_id' => $payment['id'],
            'status'     => $payment['status'],   // CONFIRMED | PENDING | DECLINED
            'invoice_url' => $payment['invoiceUrl'] ?? null,
        ];
    }

    // ── Consultar status ──────────────────────────────────────────────────

    public function getPaymentStatus(string $paymentId): ?string
    {
        $response = $this->get("/payments/{$paymentId}");

        return $response->successful() ? $response->json('status') : null;
    }

    // ── HTTP helpers ──────────────────────────────────────────────────────

    private function get(string $path, array $query = []): Response
    {
        return Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}{$path}", $query);
    }

    private function post(string $path, array $data): Response
    {
        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}{$path}", $data);
    }

    private function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
    }
}
