<?php

namespace App\Services\Payment;

/**
 * Resultado de uma cobrança processada pelo gateway.
 */
readonly class PaymentResult
{
    public function __construct(
        public string  $transactionId,  // ID da cobrança no gateway
        public string  $status,         // status bruto do gateway (ex: CONFIRMED, PENDING...)
        public string  $method,         // 'pix' | 'credit_card'
        public float   $amount,

        // PIX
        public ?string $pixCopyPaste = null,
        public ?string $pixQrCode    = null,   // imagem base64
        public ?string $pixExpiresAt = null,

        // Boleto
        public ?string $boletoDigitableLine = null,
        public ?string $boletoBarcode       = null,
        public ?string $boletoDueDate       = null,

        // Comum
        public ?string $invoiceUrl   = null,
    ) {}

    /**
     * O pagamento foi aprovado/confirmado?
     */
    public function isApproved(): bool
    {
        return in_array($this->status, ['CONFIRMED', 'RECEIVED', 'paid', 'PAID', 'AUTHORIZED', 'approved'], true);
    }

    /**
     * Converte para o array que OrderService::attachPaymentData() espera.
     */
    public function toStorageArray(int $installments = 1, float $installmentValue = 0.0, bool $interestFree = true): array
    {
        return array_filter([
            'payment_id'          => $this->transactionId,
            'status'              => $this->status,
            'pix_qrcode'          => $this->pixQrCode,
            'pix_copy_paste'      => $this->pixCopyPaste,
            'expires_at'          => $this->pixExpiresAt,
            'boleto_digitable_line' => $this->boletoDigitableLine,
            'boleto_barcode'        => $this->boletoBarcode,
            'boleto_due_date'       => $this->boletoDueDate,
            'invoice_url'           => $this->invoiceUrl,
            'installments'        => $installments > 1 ? $installments : null,
            'installment_value'   => $installments > 1 ? $installmentValue : null,
            'interest_free'       => $installments > 1 ? $interestFree : null,
            'total_with_interest' => ($installments > 1 && ! $interestFree) ? round($installmentValue * $installments, 2) : null,
        ], fn ($v) => $v !== null);
    }
}
