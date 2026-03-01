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
$contactPhone = $storeWhatsapp ?: $storePhone;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $storeName) — {{ $storeName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @yield('head')
</head>

<body class="min-h-screen @yield('body-class', 'bg-gray-50') flex flex-col antialiased">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-16">

                {{-- Logo --}}
                <a href="/" class="shrink-0">
                    @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-8 w-auto">
                    @else
                    <span class="text-xl font-bold text-gray-900 tracking-tight">{{ $storeName }}</span>
                    @endif
                </a>

                {{-- Steps (injetado pelo checkout via @push, vazio nas demais páginas) --}}
                <div class="flex-1 flex justify-center px-4">
                    @stack('header-steps')
                </div>

                {{-- Telefone de atendimento --}}
                @if ($contactPhone)
                <a href="tel:{{ preg_replace('/\D/', '', $contactPhone) }}"
                    class="hidden sm:block text-sm text-gray-600 hover:text-gray-900 transition-colors shrink-0">
                    <p class="text-xs text-gray-400 mb-0.5">Central de Atendimento</p>
                    <p>{{ $contactPhone }}</p>
                </a>
                @endif

            </div>
        </div>
    </header>

    {{-- Slot full-width antes do container (ex: mobile nav do painel) --}}
    @yield('before-main')

    {{-- Conteúdo da página --}}
    <main class="@yield('main-class', 'flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-10 pb-16')">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col items-center gap-6">

                {{-- Logo --}}
                <a href="/" class="shrink-0 text-center">
                    @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-7 w-auto">
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
                    <p>Atendimento: {{ $storeHours }}</p>
                    @endif
                    @if ($storePhone || $storeEmail)
                    <p>
                        @if ($storePhone){{ $storePhone }}@endif
                        @if ($storePhone && $storeEmail) &nbsp;·&nbsp; @endif
                        @if ($storeEmail){{ $storeEmail }}@endif
                    </p>
                    @endif
                </div>

            </div>

            <div class="mt-6 pt-6 border-t border-gray-100 text-center text-xs text-gray-500">
                © {{ date('Y') }} {{ $storeName }}. Todos os direitos reservados.
                Desevolvido por Targos
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>

</html>