@php
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

$storeName = Setting::get('store_name', config('app.name'));
$storeLogo = Setting::get('store_logo', '');
$storePhone = Setting::get('store_phone', '');
$storeWhatsapp = Setting::get('store_whatsapp', '');
$storeEmail = Setting::get('store_email', '');
$storeAddress = Setting::get('store_address', '');
$storeCnpj = Setting::get('store_cpf_cnpj', '');
$storeRazaoSocial = Setting::get('store_razao_social', '');
$storeHours = Setting::get('store_hours', '');

$logoUrl = $storeLogo ? Storage::disk('public')->url($storeLogo) : null;
$contactPhone = $storePhone;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $storeName) — {{ $storeName }}</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @livewireScriptConfig
    @stack('head')
    @yield('head')
</head>

<body class="min-h-screen @yield('body-class', 'bg-gray-50') flex flex-col antialiased">

    {{-- Header --}}
    <header class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between gap-6">

                {{-- Logo --}}
                <a href="/" class="shrink-0">
                    @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-7 sm:h-10 w-auto">
                    @else
                    <span class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">{{ $storeName }}</span>
                    @endif
                </a>

                {{-- Continuar comprando --}}
                <a href="/" class="inline-flex items-center gap-1.5 text-xs ms-auto text-gray-600 hover:text-black transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>Continuar comprando</span>
                </a>

                {{-- Telefone de atendimento --}}
                @if ($contactPhone)
                    <div class="hidden lg:flex items-center gap-2.5 shrink-0 group">
                        <div class="w-10 h-10 text-gray-900 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6v.75Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 leading-none mb-1">Central de Atendimento</p>
                            <p class="text-sm font-semibold text-gray-900 leading-none group-hover:text-gray-600 transition-colors">{{ $contactPhone }}</p>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </header>

    {{-- Slot full-width antes do container (ex: mobile nav do painel) --}}
    @yield('before-main')

    {{-- Conteúdo da página --}}
    <main class="@yield('main-class', 'flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12')">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col items-center gap-6">

                {{-- Logo --}}
                <a href="/" class="shrink-0 text-center">
                    @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-8 w-auto">
                    @else
                    <span class="text-lg font-bold text-gray-800">{{ $storeName }}</span>
                    @endif
                </a>

                {{-- Informações da loja --}}
                <div class="text-xs text-gray-500 text-center space-y-1">
                    <p>
                        @if ($storeRazaoSocial)
                        {{ $storeRazaoSocial }}
                        @endif
                        @if ($storeCnpj)
                        &nbsp;·&nbsp; CNPJ: {{ $storeCnpj }}
                        @endif
                    </p>
                    @if ($storeAddress)
                    <p>{{ $storeAddress }}</p>
                    @endif
                    @if ($storeHours)
                    <p>Atendimento: {{ $storeHours }} &nbsp;·&nbsp;
                        @endif
                        @if ($storePhone || $storeEmail)

                        @if ($storePhone){{ $storePhone }}@endif
                        @if ($storePhone && $storeEmail) &nbsp;·&nbsp; @endif
                        @if ($storeEmail){{ $storeEmail }}@endif
                    </p>
                    @endif
                    <p>© {{ date('Y') }} {{ $storeName }}. Todos os direitos reservados.</p>
                </div>

            </div>

            <div class="mt-6 pt-6 border-t border-gray-100 text-center text-xs text-gray-500">

                Desevolvido por Targos
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>