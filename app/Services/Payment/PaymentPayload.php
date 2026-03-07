<?php

namespace App\Services\Payment;

/**
 * Dados necessários para criar uma cobrança em qualquer gateway.
 *
 * Campos de cartão (card*) e endereço de cobrança (billing*) são usados
 * apenas quando method = 'credit_card'. Para PIX, são ignorados.
 */
readonly class PaymentPayload
{
    public function __construct(
        // Método e valor
        public string  $method,         // 'pix' | 'credit_card'
        public float   $amount,
        public string  $description,
        public string  $reference,      // referência interna (order_number, cart-{id}, etc.)

        // Dados do cliente
        public string  $customerName,
        public string  $customerEmail,
        public ?string $customerCpfCnpj = null,
        public ?string $customerPhone   = null,

        // Dados do cartão (credit_card)
        public ?string $cardHolder      = null,
        public ?string $cardNumber      = null,
        public ?string $cardExpiryMonth = null,
        public ?string $cardExpiryYear  = null,   // 4 dígitos: "2028"
        public ?string $cardCvv         = null,

        // Endereço de cobrança (cartão anti-fraude / boleto PagBank)
        public ?string $billingPostalCode     = null,
        public ?string $billingAddressNumber  = null,
        public ?string $billingStreet         = null,
        public ?string $billingNeighborhood   = null,
        public ?string $billingCity           = null,
        public ?string $billingState          = null,

        // Parcelamento (credit_card)
        public int     $installments      = 1,
        public float   $installmentValue  = 0.0,   // valor de cada parcela já com juros
        public bool    $interestFree      = true,

        // Cartão criptografado/tokenizado (PagBank SDK / MercadoPago.js)
        public ?string $encryptedCard        = null,
        public ?string $cardPaymentMethodId  = null,  // MercadoPago: 'visa', 'master', etc.
        public ?int    $cardIssuerId         = null,   // MercadoPago: issuer ID
    ) {}
}
