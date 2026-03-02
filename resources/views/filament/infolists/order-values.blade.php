@php
    $record          = $getRecord();
    $paymentData     = $record->payment_data ?? [];
    $cardInterest    = (float) ($paymentData['card_interest'] ?? 0);
    $installments    = (int)   ($paymentData['installments'] ?? 1);
    $installmentValue = (float) ($paymentData['installment_value'] ?? 0);
    $interestFree    = (bool)  ($paymentData['interest_free'] ?? true);
@endphp

<div class="text-sm space-y-1.5 py-1">

    {{-- Subtotal --}}
    <div class="flex items-center justify-between">
        <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
        <span class="text-gray-900 dark:text-white">
            R$ {{ number_format((float) $record->subtotal, 2, ',', '.') }}
        </span>
    </div>

    {{-- Frete --}}
    <div class="flex items-center justify-between">
        <span class="text-gray-500 dark:text-gray-400">
            Frete
            @if ($record->shipping_method)
                <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">({{ $record->shipping_method }})</span>
            @endif
        </span>
        <span class="text-gray-900 dark:text-white">
            R$ {{ number_format((float) $record->shipping_cost, 2, ',', '.') }}
        </span>
    </div>

    {{-- Desconto cupom --}}
    @if ((float) $record->discount > 0)
        <div class="flex items-center justify-between">
            <span class="text-gray-500 dark:text-gray-400">
                Desconto cupom
                @if ($record->coupon_code)
                    <span class="ml-1 inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/30">
                        {{ $record->coupon_code }}
                    </span>
                @endif
            </span>
            <span class="text-green-600 dark:text-green-400">
                − R$ {{ number_format((float) $record->discount, 2, ',', '.') }}
            </span>
        </div>
    @endif

    {{-- Desconto PIX --}}
    @if ((float) $record->pix_discount > 0)
        <div class="flex items-center justify-between">
            <span class="text-gray-500 dark:text-gray-400">Desconto PIX</span>
            <span class="text-green-600 dark:text-green-400">
                − R$ {{ number_format((float) $record->pix_discount, 2, ',', '.') }}
            </span>
        </div>
    @endif

    {{-- Juros de parcelamento --}}
    @if ($cardInterest > 0)
        <div class="flex items-center justify-between">
            <span class="text-gray-500 dark:text-gray-400">Juros de parcelamento</span>
            <span class="text-orange-600 dark:text-orange-400">
                + R$ {{ number_format($cardInterest, 2, ',', '.') }}
            </span>
        </div>
    @endif

    {{-- Divisor + Total --}}
    <div class="flex items-center justify-between border-t border-gray-200 dark:border-white/10 pt-2 mt-2">
        <span class="font-semibold text-gray-900 dark:text-white">Total</span>
        <span class="font-bold text-base text-gray-900 dark:text-white">
            R$ {{ number_format((float) $record->total, 2, ',', '.') }}
        </span>
    </div>

    {{-- Detalhe do parcelamento (abaixo do total) --}}
    @if ($installments > 1)
        <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500">
            <span>Parcelamento</span>
            <span>
                {{ $installments }}× de R$ {{ number_format($installmentValue, 2, ',', '.') }}
                {{ $interestFree ? 'sem juros' : 'com juros' }}
            </span>
        </div>
    @endif

</div>
