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
        <h3 class="text-sm font-medium text-gray-800 line-clamp-2 leading-snug mb-1 flex-1">
            {{ $entry->displayName }}
        </h3>

        @if ($entry->product->rating_count > 0)
        <div class="flex items-center gap-1 mb-2">
            <div class="flex gap-0.5">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-3.5 h-3.5 {{ $i <= round($entry->product->rating_avg) ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                @endfor
            </div>
            <span class="text-xs text-gray-400">({{ $entry->product->rating_count }})</span>
        </div>
        @endif

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
