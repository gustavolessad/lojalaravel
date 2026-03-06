@props(['entry'])

@php
    $calc      = app(\App\Services\Payment\PaymentCalculator::class);
    $cardMode  = $calc->cardDisplayMode();
    $pixP      = $calc->pixPrice($entry->price);
    $instLabel = $calc->bestFreeInstallmentLabel($entry->price);
@endphp

<a href="{{ $entry->url }}"
   @class([
       'group bg-white  flex flex-col transition-shadow',
       'border-gray-100 opacity-60'      => ! $entry->inStock,
   ])>

    {{-- Imagem --}}
    <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 overflow-hidden relative">
        @if ($entry->imageUrl)
            <img src="{{ $entry->imageUrl }}"
                 alt="{{ $entry->displayName }}"
                 loading="lazy"
                 @class([
                     'w-full h-full object-cover rounded-xl transition-transform duration-300',
                     'group-hover:scale-105' => $entry->inStock,
                     'grayscale'             => ! $entry->inStock,
                 ])>
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif

        {{-- Badges --}}
        @if ($entry->inStock)
            <div class="absolute top-2 left-2 flex flex-col gap-1 items-start">
                @if ($entry->isNew)
                    <span class="text-xs font-semibold px-2 py-0.5 bg-emerald-500 text-white rounded-full">Novo</span>
                @endif
                @if ($entry->isOnSale)
                    <span class="text-xs font-semibold px-2 py-0.5 bg-red-500 text-white rounded-full">Promoção</span>
                @endif
            </div>
        @endif
    </div>

    {{-- Informações --}}
    <div class="mt-3 flex flex-col flex-1">
        <h3 class="text-sm font-medium text-gray-800 line-clamp-2 leading-snug mb-2 flex-1">
            {{ $entry->displayName }}
        </h3>

        <div class="mt-auto">
            @if (! $entry->inStock)
                <span class="text-sm font-medium text-gray-400">Indisponível no momento</span>
            @else
                @if ($entry->originalPrice)
                    <div class="flex items-baseline gap-2">
                        <span class="text-xs text-gray-400 line-through">
                            R$ {{ number_format($entry->originalPrice, 2, ',', '.') }}
                        </span>
                        <span class="text-base font-bold text-green-700">
                            R$ {{ number_format($entry->price, 2, ',', '.') }}
                        </span>
                    </div>
                @else
                    <span class="text-base font-bold text-green-700">
                        R$ {{ number_format($entry->price, 2, ',', '.') }}
                    </span>
                @endif

                @if (($cardMode === 'pix' || $cardMode === 'both') && $pixP)
                    <p class="text-xs text-green-600 mt-0.5 font-bold">
                        R$ {{ number_format($pixP, 2, ',', '.') }} no PIX
                    </p>
                @endif
                @if (($cardMode === 'installments' || $cardMode === 'both') && $instLabel)
                    <p class="text-xs text-gray-900 mt-0.5 font-medium">{{ $instLabel }}</p>
                @endif
            @endif
        </div>
    </div>
</a>
