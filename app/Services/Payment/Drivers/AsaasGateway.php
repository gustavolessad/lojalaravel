<?php

namespace App\Services\Payment\Drivers;

use App\Models\Setting;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentPayload;
use App\Services\Payment\PaymentResult;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasGateway implements PaymentGatewayInterface
{
    private string $baseUrl;
    private string $apiKey;
    private ?array $lastError = null;

    public function __construct()
    {
        $sandbox      = (bool) Setting::get('payment_asaas_sandbox', true);
        $this->apiKey = (string) Setting::get('payment_asaas_token', '');
        $this->baseUrl = $sandbox
            ? 'https://sandbox.asaas.com/api/v3'
            : 'https://api.asaas.com/api/v3';
    }

    // ── Interface ─────────────────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function createCharge(PaymentPayload $payload): PaymentResult
    {
        $gatewayCustomerId = $this->findOrCreateCustomer(
            name:     $payload->customerName,
            email:    $payload->customerEmail,
            cpfCnpj: $payload->customerCpfCnpj,
            phone:    $payload->customerPhone,
        );

        if (! $gatewayCustomerId) {
            throw new \RuntimeException('Falha ao localizar/criar cliente no gateway de pagamento.');
        }

        return match ($payload->method) {
            'pix'         => $this->doPixCharge($payload, $gatewayCustomerId),
            'credit_card' => $this->doCardCharge($payload, $gatewayCustomerId),
            'boleto'      => $this->doBoletoCharge($payload, $gatewayCustomerId),
            default       => throw new \InvalidArgumentException("Método de pagamento [{$payload->method}] não suportado pelo Asaas."),
        };
    }

    public function getLastError(): ?array
    {
        return $this->lastError;
    }

    // ── PIX ───────────────────────────────────────────────────────────────

    private function doPixCharge(PaymentPayload $payload, string $gatewayCustomerId): PaymentResult
    {
        $response = $this->post('/payments', [
            'customer'          => $gatewayCustomerId,
            'billingType'       => 'PIX',
            'value'             => $payload->amount,
            'dueDate'           => now()->addDay()->format('Y-m-d'),
            'description'       => $payload->description,
            'externalReference' => $payload->reference,
        ]);

        if (! $response->successful()) {
            $this->lastError = $response->json();
            Log::error('AsaasGateway: erro ao criar PIX', [
                'status' => $response->status(),
                'body'   => $this->lastError,
            ]);
            throw new \RuntimeException('Falha ao gerar o QR Code PIX. Tente novamente.');
        }

        $payment = $response->json();
        $pixId   = $payment['id'];

        // Busca o QR code
        $qrResponse = $this->get("/payments/{$pixId}/pixQrCode");
        $qr         = $qrResponse->json();

        return new PaymentResult(
            transactionId: $pixId,
            status:        $payment['status'],
            method:        'pix',
            amount:        $payload->amount,
            pixCopyPaste:  $qr['payload']       ?? null,
            pixQrCode:     $qr['encodedImage']  ?? null,
            pixExpiresAt:  $payment['dueDate']  ?? null,
            invoiceUrl:    $payment['invoiceUrl'] ?? null,
        );
    }

    // ── Cartão de crédito ─────────────────────────────────────────────────

    private function doCardCharge(PaymentPayload $payload, string $gatewayCustomerId): PaymentResult
    {
        $installmentValue = $payload->installmentValue > 0
            ? $payload->installmentValue
            : round($payload->amount / max(1, $payload->installments), 2);

        $holderInfo = [
            'name'          => $payload->cardHolder,
            'email'         => $payload->customerEmail,
            'cpfCnpj'       => preg_replace('/\D/', '', $payload->customerCpfCnpj ?? ''),
            'postalCode'    => preg_replace('/\D/', '', $payload->billingPostalCode ?? ''),
            'addressNumber' => $payload->billingAddressNumber ?? '',
            'phone'         => preg_replace('/\D/', '', $payload->customerPhone ?? ''),
        ];

        $response = $this->post('/payments', [
            'customer'          => $gatewayCustomerId,
            'billingType'       => 'CREDIT_CARD',
            'value'             => $payload->amount,
            'dueDate'           => now()->format('Y-m-d'),
            'description'       => $payload->description,
            'externalReference' => $payload->reference,
            'installmentCount'  => $payload->installments,
            'installmentValue'  => $installmentValue,
            'creditCard'        => [
                'holderName'  => $payload->cardHolder,
                'number'      => preg_replace('/\D/', '', $payload->cardNumber ?? ''),
                'expiryMonth' => $payload->cardExpiryMonth,
                'expiryYear'  => $payload->cardExpiryYear,
                'ccv'         => $payload->cardCvv,
            ],
            'creditCardHolderInfo' => $holderInfo,
        ]);

        if (! $response->successful()) {
            $this->lastError = $response->json();
            Log::error('AsaasGateway: erro ao cobrar cartão', [
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

        $payment = $response->json();

        return new PaymentResult(
            transactionId: $payment['id'],
            status:        $payment['status'],
            method:        'credit_card',
            amount:        $payload->amount,
            invoiceUrl:    $payment['invoiceUrl'] ?? null,
        );
    }

    // ── Boleto ──────────────────────────────────────────────────────────

    private function doBoletoCharge(PaymentPayload $payload, string $gatewayCustomerId): PaymentResult
    {
        $dueDays = max(1, (int) Setting::get('payment_boleto_due_days', 3));
        $dueDate = now()->addDays($dueDays)->format('Y-m-d');

        $response = $this->post('/payments', [
            'customer'          => $gatewayCustomerId,
            'billingType'       => 'BOLETO',
            'value'             => $payload->amount,
            'dueDate'           => $dueDate,
            'description'       => $payload->description,
            'externalReference' => $payload->reference,
        ]);

        if (! $response->successful()) {
            $this->lastError = $response->json();
            Log::error('AsaasGateway: erro ao criar boleto', [
                'status' => $response->status(),
                'body'   => $this->lastError,
            ]);
            throw new \RuntimeException('Falha ao gerar o boleto. Tente novamente.');
        }

        $payment  = $response->json();
        $boletoId = $payment['id'];

        // Busca a linha digitável (não vem na resposta de criação)
        $idFieldResponse = $this->get("/payments/{$boletoId}/identificationField");
        $idField         = $idFieldResponse->json();

        return new PaymentResult(
            transactionId:       $boletoId,
            status:              $payment['status'],
            method:              'boleto',
            amount:              $payload->amount,
            boletoDigitableLine: $idField['identificationField'] ?? null,
            boletoBarcode:       $idField['barCode'] ?? $payment['barCode'] ?? null,
            boletoDueDate:       $payment['dueDate'] ?? $dueDate,
            invoiceUrl:          $payment['bankSlipUrl'] ?? $payment['invoiceUrl'] ?? null,
        );
    }

    // ── Cliente ───────────────────────────────────────────────────────────

    private function findOrCreateCustomer(
        string  $name,
        string  $email,
        ?string $cpfCnpj,
        ?string $phone,
    ): ?string {
        $cleanCpfCnpj = $cpfCnpj ? preg_replace('/\D/', '', $cpfCnpj) : null;

        // Tenta buscar por CPF/CNPJ
        if ($cleanCpfCnpj) {
            $response = $this->get('/customers', ['cpfCnpj' => $cleanCpfCnpj]);
            $data     = $response->json();

            if (! empty($data['data'][0]['id'])) {
                return $data['data'][0]['id'];
            }
        }

        // Cria novo cliente
        $payload = [
            'name'  => $name,
            'email' => $email,
            'phone' => preg_replace('/\D/', '', $phone ?? ''),
        ];

        if ($cleanCpfCnpj) {
            $payload['cpfCnpj'] = $cleanCpfCnpj;
        }

        $response = $this->post('/customers', $payload);

        if (! $response->successful()) {
            Log::error('AsaasGateway: erro ao criar cliente', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return null;
        }

        return $response->json('id');
    }

    // ── Atualização de cobrança ───────────────────────────────────────────

    /**
     * Atualiza a descrição e a referência externa de uma cobrança já criada.
     * Chamado após a criação do pedido para substituir "Compra na loja" pelo
     * número real do pedido.
     */
    public function updatePaymentReference(string $paymentId, string $description, string $externalReference): void
    {
        $response = $this->put("/payments/{$paymentId}", [
            'description'       => $description,
            'externalReference' => $externalReference,
        ]);

        if (! $response->successful()) {
            Log::warning('AsaasGateway: falha ao atualizar referência do pagamento', [
                'paymentId' => $paymentId,
                'status'    => $response->status(),
                'body'      => $response->json(),
            ]);
        }
    }

    // ── HTTP ──────────────────────────────────────────────────────────────

    private function get(string $path, array $query = []): Response
    {
        try {
            return Http::withHeaders($this->headers())
                ->timeout(15)
                ->get("{$this->baseUrl}{$path}", $query);
        } catch (\Throwable $e) {
            Log::error('AsaasGateway: falha de conexão (GET)', ['path' => $path, 'error' => $e->getMessage()]);
            return Http::response(['error' => $e->getMessage()], 503);
        }
    }

    private function put(string $path, array $data): Response
    {
        try {
            return Http::withHeaders($this->headers())
                ->timeout(15)
                ->put("{$this->baseUrl}{$path}", $data);
        } catch (\Throwable $e) {
            Log::error('AsaasGateway: falha de conexão (PUT)', ['path' => $path, 'error' => $e->getMessage()]);
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
            Log::error('AsaasGateway: falha de conexão (POST)', ['path' => $path, 'error' => $e->getMessage()]);
            return Http::response(['error' => $e->getMessage()], 503);
        }
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
