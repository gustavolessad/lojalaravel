@php
    use App\Models\Setting;
    use Illuminate\Support\Facades\Storage;

    $storeName  = Setting::get('store_name', config('app.name'));
    $storeLogo  = Setting::get('store_logo', '');
    $storePhone = Setting::get('store_phone', '');
    $storeWhatsapp = Setting::get('store_whatsapp', '');
    $storeEmail = Setting::get('store_email', '');
    $storeAddress = Setting::get('store_address', '');
    $storeCnpj  = Setting::get('store_cpf_cnpj', '');
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
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="/" class="shrink-0">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-8 w-auto">
                    @else
                        <span class="text-xl font-bold text-gray-900 tracking-tight">{{ $storeName }}</span>
                    @endif
                </a>

                {{-- Telefone de atendimento --}}
                @if ($contactPhone)
                    <a href="tel:{{ preg_replace('/\D/', '', $contactPhone) }}"
                       class="hidden sm:flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                        {{ $contactPhone }}
                    </a>
                @endif

            </div>
        </div>
    </header>

    {{-- Conteúdo da página --}}
    @yield('content')

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-6">

                {{-- Logo --}}
                <a href="/" class="shrink-0">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-7 w-auto opacity-80">
                    @else
                        <span class="text-lg font-bold text-gray-800">{{ $storeName }}</span>
                    @endif
                </a>

                {{-- Informações da loja --}}
                <div class="text-xs text-gray-400 text-center md:text-right space-y-1">
                    @if ($storeRazaoSocial)
                        <p class="font-medium text-gray-500">{{ $storeRazaoSocial }}</p>
                    @endif
                    @if ($storeCnpj)
                        <p>CNPJ: {{ $storeCnpj }}</p>
                    @endif
                    @if ($storeAddress)
                        <p>{{ $storeAddress }}</p>
                    @endif
                    @if ($storeHours)
                        <p>{{ $storeHours }}</p>
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

            <div class="mt-6 pt-6 border-t border-gray-100 text-center text-xs text-gray-300">
                © {{ date('Y') }} {{ $storeName }}. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
