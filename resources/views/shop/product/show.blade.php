@extends('layouts.shop')

@section('title', $product->seo_title ?: $product->name)

@section('content')

    {{-- Breadcrumb --}}
    @if ($product->categories->isNotEmpty())
        @php $firstCategory = $product->categories->first(); @endphp
        <nav class="flex items-center gap-1.5 text-sm text-gray-500 mb-6 flex-wrap">
            <a href="/" class="hover:text-gray-900 transition-colors">Início</a>
            @foreach ($firstCategory->breadcrumb as $crumb)
                <span class="text-gray-300">/</span>
                <a href="{{ $crumb->url }}" class="hover:text-gray-900 transition-colors">
                    {{ $crumb->name }}
                </a>
            @endforeach
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">{{ $product->name }}</span>
        </nav>
    @endif

    @livewire('shop.product-detail', ['product' => $product])

@endsection

@push('scripts')
{{-- ═══════════════════════════════════════════════════════════════
     MODAL "Produto adicionado ao carrinho" (Alpine.js)
     Ouve o evento disparado pelo componente Livewire
════════════════════════════════════════════════════════════════ --}}
<div
    x-data="{
        open: false,
        name: '',
        image: '',
        variant: '',
        close() { this.open = false },
    }"
    @product-added-to-cart.window="
        name    = $event.detail.name;
        image   = $event.detail.image;
        variant = $event.detail.variant;
        open    = true;
    "
    @keydown.escape.window="close()"
    x-cloak
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-6"
        style="background: rgba(0,0,0,.45)"
        @click.self="close()"
    >
        {{-- Painel do modal --}}
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6"
        >
            {{-- Fechar --}}
            <button
                @click="close()"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
                aria-label="Fechar"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Conteúdo --}}
            <div class="flex items-start gap-4 mb-5">
                {{-- Ícone de check --}}
                <div class="w-11 h-11 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                {{-- Informações do produto --}}
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-sm font-semibold text-gray-900">Produto adicionado!</p>
                    <p class="text-sm text-gray-600 mt-0.5 truncate" x-text="name"></p>
                    <p
                        x-show="variant"
                        class="text-xs text-indigo-600 font-medium mt-1 bg-indigo-50 inline-block px-2 py-0.5 rounded-full"
                        x-text="variant"
                    ></p>
                </div>
            </div>

            {{-- Botões de ação --}}
            <div class="flex flex-col gap-2">
                <a
                    href="{{ route('cart.index') }}"
                    class="w-full py-3 px-4 bg-indigo-600 text-white text-sm font-semibold
                           rounded-xl text-center hover:bg-indigo-700 transition-colors"
                >
                    Ver Carrinho / Finalizar Compra
                </a>
                <button
                    @click="close()"
                    class="w-full py-3 px-4 bg-gray-100 text-gray-700 text-sm font-medium
                           rounded-xl hover:bg-gray-200 transition-colors"
                >
                    Continuar Comprando
                </button>
            </div>
        </div>
    </div>
</div>
@endpush
