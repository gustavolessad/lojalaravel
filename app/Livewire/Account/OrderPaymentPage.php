<?php

namespace App\Livewire\Account;

use App\Models\Order;
use App\Rules\ValidCpf;
use App\Rules\ValidCnpj;
use App\Services\AsaasService;
use App\Services\OrderService;
use App\Services\PaymentCalculator;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OrderPaymentPage extends Component
{
    public Order $order;

    // ── Método de pagamento ───────────────────────────────────────────────
    public string $paymentMethod = 'pix';

    // ── PIX ───────────────────────────────────────────────────────────────
    public ?array $pixData      = null;   // qrcode, copy_paste, expires_at
    public bool   $generatingPix = false;

    // ── Cartão ────────────────────────────────────────────────────────────
    public string $cardHolder   = '';
    public string $cardNumber   = '';
    public string $cardExpiry   = '';
    public string $cardCvv      = '';
    public int    $installments = 1;

    // ── UI ────────────────────────────────────────────────────────────────
    public bool    $processing   = false;
    public ?string $errorMessage = null;

    // ── Mount ─────────────────────────────────────────────────────────────

    public function mount(string $orderNumber): void
    {
        $customer = Auth::guard('customer')->user();

        if (! $customer) {
            $this->redirect(route('account.login'), navigate: false);
            return;
        }

        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', $customer->id)
            ->with(['items.product', 'items.variant', 'payments', 'orderEvents'])
            ->firstOrFail();

        // Pedido já pago: redireciona para a tela do pedido
        if ($order->payment_status === 'paid') {
            session()->flash('info', 'Este pedido já foi pago.');
            $this->redirect(route('account.orders.show', $order->order_number), navigate: true);
            return;
        }

        // Pedido cancelado ou reembolsado: não pode pagar
        if (in_array($order->status, ['cancelled', 'refunded'])) {
            session()->flash('error', 'Este pedido não pode ser pago.');
            $this->redirect(route('account.orders.show', $order->order_number), navigate: true);
            return;
        }

        $this->order         = $order;
        $this->paymentMethod = $order->payment_method;

        // Se já é PIX, tenta exibir QR existente (se ainda válido)
        if ($this->paymentMethod === 'pix') {
            $this->loadExistingPixData();
        }
    }

    // ── Computed ──────────────────────────────────────────────────────────

    #[Computed]
    public function installmentOptions(): array
    {
        return app(PaymentCalculator::class)->installmentOptions((float) $this->order->total);
    }

    #[Computed]
    public function pixTotal(): ?float
    {
        return app(PaymentCalculator::class)->pixPrice((float) $this->order->total);
    }

    #[Computed]
    public function pixSavings(): float
    {
        return app(PaymentCalculator::class)->pixSavings((float) $this->order->total);
    }

    // ── Trocar método de pagamento ────────────────────────────────────────

    public function switchMethod(string $method): void
    {
        if (! in_array($method, ['pix', 'credit_card'])) {
            return;
        }

        $oldMethod = $this->paymentMethod;

        if ($method === $oldMethod) {
            return;
        }

        $this->paymentMethod = $method;
        $this->errorMessage  = null;
        $this->pixData       = null;

        // Registra no histórico se o método mudou em relação ao original do pedido
        if ($method !== $this->order->payment_method) {
            $labels = ['pix' => 'PIX', 'credit_card' => 'Cartão de crédito'];
            app(OrderService::class)->logEvent(
                $this->order,
                'payment_method_changed',
                "Método de pagamento alterado para {$labels[$method]} na tentativa de pagamento",
                ['from' => $oldMethod, 'to' => $method],
            );
        }

        if ($method === 'pix') {
            $this->loadExistingPixData();
        }
    }

    // ── PIX ───────────────────────────────────────────────────────────────

    public function generatePix(): void
    {
        $this->generatingPix = true;
        $this->errorMessage  = null;

        try {
            $asaas = app(AsaasService::class);

            if (! $asaas->isConfigured()) {
                $this->errorMessage = 'O gateway de pagamento não está configurado. Entre em contato com a loja.';
                return;
            }

            $asaasCustomerId = $asaas->findOrCreateCustomer($this->order);

            if (! $asaasCustomerId) {
                $this->errorMessage = 'Falha ao conectar com o gateway de pagamento. Tente novamente.';
                return;
            }

            // Atualiza o método do pedido para PIX (caso tenha sido criado como cartão)
            if ($this->order->payment_method !== 'pix') {
                $this->order->update(['payment_method' => 'pix']);
                $this->order->refresh();
            }

            $paymentData = $asaas->createPixPayment($this->order, $asaasCustomerId);

            if (! $paymentData) {
                app(OrderService::class)->recordFailedPayment(
                    order:        $this->order,
                    method:       'pix',
                    amount:       (float) $this->order->total,
                    errorMessage: 'Falha ao gerar QR Code PIX via Asaas',
                );
                $this->errorMessage = 'Falha ao gerar o QR Code PIX. Tente novamente.';
                return;
            }

            app(OrderService::class)->attachPaymentData(
                order:     $this->order,
                paymentId: $paymentData['payment_id'],
                data:      $paymentData,
            );

            $this->order->refresh();

            $this->pixData = [
                'qrcode'     => $paymentData['pix_qrcode']    ?? null,
                'copy_paste' => $paymentData['pix_copy_paste'] ?? null,
                'expires_at' => $paymentData['expires_at']    ?? null,
            ];

            // Limpa sessão de retry do checkout (esse pedido agora tem novo PIX)
            session()->forget('checkout_pending_order_id');

        } finally {
            $this->generatingPix = false;
        }
    }

    // ── Cartão ────────────────────────────────────────────────────────────

    public function updatedCardNumber(): void
    {
        $this->cardNumber = preg_replace('/\D/', '', $this->cardNumber);
    }

    public function payWithCard(): void
    {
        $this->errorMessage = null;
        $this->processing   = true;

        try {
            $this->validate([
                'cardHolder' => 'required|string|min:3',
                'cardNumber' => 'required|digits_between:13,19',
                'cardExpiry' => ['required', 'regex:/^\d{2}\/\d{2}$/'],
                'cardCvv'    => 'required|digits_between:3,4',
            ], [
                'cardHolder.required'       => 'Informe o nome no cartão.',
                'cardNumber.required'       => 'Informe o número do cartão.',
                'cardNumber.digits_between' => 'Número de cartão inválido.',
                'cardExpiry.required'       => 'Informe a validade (MM/AA).',
                'cardExpiry.regex'          => 'Validade inválida. Use MM/AA.',
                'cardCvv.required'          => 'Informe o código de segurança.',
            ]);

            $asaas = app(AsaasService::class);

            if (! $asaas->isConfigured()) {
                $this->errorMessage = 'O gateway de pagamento não está configurado. Entre em contato com a loja.';
                return;
            }

            $asaasCustomerId = $asaas->findOrCreateCustomer($this->order);

            if (! $asaasCustomerId) {
                $this->errorMessage = 'Falha ao conectar com o gateway de pagamento. Tente novamente.';
                return;
            }

            // Atualiza o método do pedido para cartão (caso tenha sido criado como PIX)
            if ($this->order->payment_method !== 'credit_card') {
                $this->order->update(['payment_method' => 'credit_card']);
                $this->order->refresh();
            }

            $expiryParts = explode('/', $this->cardExpiry);
            $month       = $expiryParts[0] ?? '';
            $year        = $expiryParts[1] ?? '';

            $orderTotal        = (float) $this->order->total;
            $calc              = app(PaymentCalculator::class);
            $installmentOpts   = $calc->installmentOptions($orderTotal);
            $chosenInstallment = collect($installmentOpts)->firstWhere('value', $this->installments);
            $installmentValue  = $chosenInstallment['installment_value'] ?? round($orderTotal / $this->installments, 2);
            $interestFree      = $chosenInstallment['interest_free'] ?? true;

            $paymentData = $asaas->createCreditCardPayment(
                order:           $this->order,
                asaasCustomerId: $asaasCustomerId,
                card: [
                    'holderName'  => $this->cardHolder,
                    'number'      => $this->cardNumber,
                    'expiryMonth' => $month,
                    'expiryYear'  => '20' . $year,
                    'ccv'         => $this->cardCvv,
                ],
                installments: $this->installments,
            );

            if (! $paymentData) {
                $errorDesc = $asaas->lastError['errors'][0]['description'] ?? null;
                app(OrderService::class)->recordFailedPayment(
                    order:        $this->order,
                    method:       'credit_card',
                    amount:       $orderTotal,
                    errorMessage: $errorDesc,
                );
                $this->errorMessage = $errorDesc ?? 'Verifique os dados do cartão e tente novamente.';
                return;
            }

            $paymentData['installments']        = $this->installments;
            $paymentData['installment_value']   = $installmentValue;
            $paymentData['interest_free']       = $interestFree;
            $paymentData['total_with_interest'] = round($installmentValue * $this->installments, 2);

            $orderService = app(OrderService::class);
            $orderService->attachPaymentData(
                order:     $this->order,
                paymentId: $paymentData['payment_id'],
                data:      $paymentData,
            );

            if (in_array($paymentData['status'] ?? '', ['CONFIRMED', 'RECEIVED'])) {
                $orderService->markAsPaid(
                    order:     $this->order,
                    paymentId: $paymentData['payment_id'],
                );
            }

            // Limpa sessão de retry do checkout
            session()->forget('checkout_pending_order_id');

            $this->redirect(route('order.confirmation', $this->order->order_number), navigate: false);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('OrderPaymentPage error', [
                'order' => $this->order->order_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->errorMessage = 'Ocorreu um erro inesperado. Por favor, tente novamente.';
        } finally {
            $this->processing = false;
        }
    }

    // ── Helpers privados ──────────────────────────────────────────────────

    private function loadExistingPixData(): void
    {
        $pd = $this->order->payment_data ?? [];

        if (
            ! empty($pd['pix_qrcode'])    &&
            ! empty($pd['pix_copy_paste']) &&
            ! empty($pd['expires_at'])    &&
            now()->lt(Carbon::parse($pd['expires_at']))
        ) {
            $this->pixData = [
                'qrcode'     => $pd['pix_qrcode'],
                'copy_paste' => $pd['pix_copy_paste'],
                'expires_at' => $pd['expires_at'],
            ];
        }
    }

    // ── Render ────────────────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.account.order-payment-page');
    }
}
