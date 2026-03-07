<?php

namespace App\Services\Payment\Drivers;

use App\Models\Setting;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentPayload;
use App\Services\Payment\PaymentResult;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    private string $baseUrl = 'https://api.mercadopago.com';
    private string $token;
    private ?array $lastError = null;

    public function __construct()
    {
        $this->token = (string) Setting::get('payment_mercadopago_token', '');
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
            default       => throw new \InvalidArgumentException("Método de pagamento [{$payload->method}] não suportado pelo MercadoPago."),
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
        $names   = $this->splitName($payload->customerName);

        $response = $this->post('/v1/payments', [
            'transaction_amount' => $payload->amount,
            'payment_method_id'  => 'pix',
            'description'        => $payload->description,
            'external_reference' => $payload->reference,
            'notification_url'   => route('webhook.mercadopago'),
            'payer'              => [
                'email'          => $payload->customerEmail,
                'first_name'     => $names['first'],
                'last_name'      => $names['last'],
                'identification' => [
                    'type'   => strlen($cpfCnpj) > 11 ? 'CNPJ' : 'CPF',
                    'number' => $cpfCnpj,
                ],
            ],
        ]);

        if (! $response->successful()) {
            $this->lastError = $response->json();
            Log::error('MercadoPagoGateway: erro ao criar PIX', [
                'status' => $response->status(),
                'body'   => $this->lastError,
            ]);
            throw new \RuntimeException('Falha ao gerar o QR Code PIX. Tente novamente.');
        }

        $payment = $response->json();
        $txData  = $payment['point_of_interaction']['transaction_data'] ?? [];

        return new PaymentResult(
            transactionId: (string) ($payment['id'] ?? ''),
            status:        $payment['status'] ?? 'pending',
            method:        'pix',
            amount:        $payload->amount,
            pixCopyPaste:  $txData['qr_code'] ?? null,
            pixQrCode:     $txData['qr_code_base64'] ?? null,
            pixExpiresAt:  $txData['expiration_date'] ?? $payment['date_of_expiration'] ?? null,
        );
    }

    // ── Cartão de crédito ─────────────────────────────────────────────────

    private function doCardCharge(PaymentPayload $payload): PaymentResult
    {
        $body = [
            'transaction_amount' => $payload->amount,
            'token'              => $payload->encryptedCard,
            'description'        => $payload->description,
            'installments'       => $payload->installments,
            'external_reference' => $payload->reference,
            'notification_url'   => route('webhook.mercadopago'),
            'payer'              => [
                'email' => $payload->customerEmail,
            ],
        ];

        if ($payload->cardPaymentMethodId) {
            $body['payment_method_id'] = $payload->cardPaymentMethodId;
        }

        if ($payload->cardIssuerId) {
            $body['issuer_id'] = $payload->cardIssuerId;
        }

        $response = $this->post('/v1/payments', $body);

        if (! $response->successful()) {
            $this->lastError = $response->json();
            Log::error('MercadoPagoGateway: erro ao cobrar cartão', [
                'status' => $response->status(),
                'body'   => $this->lastError,
            ]);
            return new PaymentResult(
                transactionId: '',
                status:        'rejected',
                method:        'credit_card',
                amount:        $payload->amount,
            );
        }

        $payment = $response->json();

        return new PaymentResult(
            transactionId: (string) ($payment['id'] ?? ''),
            status:        $payment['status'] ?? 'rejected',
            method:        'credit_card',
            amount:        $payload->amount,
        );
    }

    // ── Boleto ──────────────────────────────────────────────────────────

    private function doBoletoCharge(PaymentPayload $payload): PaymentResult
    {
        $cpfCnpj = preg_replace('/\D/', '', $payload->customerCpfCnpj ?? '');
        $names   = $this->splitName($payload->customerName);
        $zip     = preg_replace('/\D/', '', $payload->billingPostalCode ?? '');

        $dueDays       = max(1, (int) Setting::get('payment_boleto_due_days', 3));
        $expirationDt  = now()->addDays($dueDays)->endOfDay()->format('Y-m-d\TH:i:s.vP');

        $response = $this->post('/v1/payments', [
            'transaction_amount' => $payload->amount,
            'payment_method_id'  => 'bolbradesco',
            'description'        => $payload->description,
            'external_reference' => $payload->reference,
            'date_of_expiration' => $expirationDt,
            'notification_url'   => route('webhook.mercadopago'),
            'payer'              => [
                'email'          => $payload->customerEmail,
                'first_name'     => $names['first'],
                'last_name'      => $names['last'],
                'identification' => [
                    'type'   => strlen($cpfCnpj) > 11 ? 'CNPJ' : 'CPF',
                    'number' => $cpfCnpj,
                ],
                'address' => [
                    'zip_code'      => $zip,
                    'street_name'   => $payload->billingStreet ?? '',
                    'street_number' => $payload->billingAddressNumber ?? '',
                    'neighborhood'  => $payload->billingNeighborhood ?? '',
                    'city'          => $payload->billingCity ?? '',
                    'federal_unit'  => $payload->billingState ?? '',
                ],
            ],
        ]);

        if (! $response->successful()) {
            $this->lastError = $response->json();
            Log::error('MercadoPagoGateway: erro ao criar boleto', [
                'status' => $response->status(),
                'body'   => $this->lastError,
            ]);
            throw new \RuntimeException('Falha ao gerar o boleto. Tente novamente.');
        }

        $payment = $response->json();

        return new PaymentResult(
            transactionId:       (string) ($payment['id'] ?? ''),
            status:              $payment['status'] ?? 'pending',
            method:              'boleto',
            amount:              $payload->amount,
            boletoDigitableLine: $payment['barcode']['content'] ?? null,
            boletoBarcode:       $payment['barcode']['content'] ?? null,
            boletoDueDate:       $payment['date_of_expiration'] ?? null,
            invoiceUrl:          $payment['transaction_details']['external_resource_url'] ?? null,
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);

        return [
            'first' => $parts[0] ?? '',
            'last'  => $parts[1] ?? $parts[0] ?? '',
        ];
    }

    // ── HTTP ──────────────────────────────────────────────────────────────

    private function get(string $path): Response
    {
        try {
            return Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}{$path}");
        } catch (\Throwable $e) {
            Log::error('MercadoPagoGateway: falha de conexão (GET)', ['path' => $path, 'error' => $e->getMessage()]);
            return Http::response(['error' => $e->getMessage()], 503);
        }
    }

    private function post(string $path, array $data): Response
    {
        try {
            return Http::withHeaders($this->headers())
                ->timeout(15)
                ->post("{$this->baseUrl}{$path}", $data);
        } catch (\Throwable $e) {
            Log::error('MercadoPagoGateway: falha de conexão (POST)', ['path' => $path, 'error' => $e->getMessage()]);
            return Http::response(['error' => $e->getMessage()], 503);
        }
    }

    private function headers(): array
    {
        return [
            'Authorization'    => "Bearer {$this->token}",
            'Content-Type'     => 'application/json',
            'Accept'           => 'application/json',
            'X-Idempotency-Key' => (string) Str::uuid(),
        ];
    }
}
