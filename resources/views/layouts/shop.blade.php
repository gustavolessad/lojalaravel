@php
use App\Models\Setting;
use App\Models\StorePage;
use Illuminate\Support\Facades\Storage;

$storeName        = Setting::get('store_name', config('app.name'));
$storeSlogan      = Setting::get('store_slogan', '');
$storeLogo        = Setting::get('store_logo', '');
$storeLogoFooter  = Setting::get('store_logo_footer', '');
$storePhone       = Setting::get('store_phone', '');
$storeWhatsapp    = Setting::get('store_whatsapp', '');
$storeEmail       = Setting::get('store_email', '');
$storeAddress     = Setting::get('store_address', '');
$storeHours       = Setting::get('store_hours', '');
$storeCnpj        = Setting::get('store_cpf_cnpj', '');
$storeRazaoSocial = Setting::get('store_razao_social', '');

$socialInstagram  = Setting::get('social_instagram', '');
$socialFacebook   = Setting::get('social_facebook', '');
$socialYoutube    = Setting::get('social_youtube', '');
$socialTiktok     = Setting::get('social_tiktok', '');
$socialPinterest  = Setting::get('social_pinterest', '');
$socialLinkedin   = Setting::get('social_linkedin', '');
$socialTwitter    = Setting::get('social_twitter', '');

$sealPayment      = Setting::get('seal_payment', '');
$sealSecurity     = Setting::get('seal_security', '');
$sealTransport    = Setting::get('seal_transport', '');

$logoUrl          = $storeLogo ? Storage::disk('public')->url($storeLogo) : null;
$logoFooterUrl    = $storeLogoFooter ? Storage::disk('public')->url($storeLogoFooter) : $logoUrl;

$sealPaymentUrl   = $sealPayment  ? Storage::disk('public')->url($sealPayment)  : null;
$sealSecurityUrl  = $sealSecurity ? Storage::disk('public')->url($sealSecurity) : null;
$sealTransportUrl = $sealTransport ? Storage::disk('public')->url($sealTransport) : null;

$storePages = StorePage::orderBy('title')->get();
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $storeName)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @livewireScriptConfig
</head>
<body class="text-gray-900 antialiased">

    {{-- ═══ Header ═══ --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center gap-4 justify-between">

                {{-- Logo --}}
                <a href="/" class="shrink-0">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-10 w-auto">
                    @else
                        <span class="text-xl font-bold text-gray-900">{{ $storeName }}</span>
                    @endif
                </a>

                {{-- Busca --}}
                @livewire('shop.search-bar')

                {{-- Nav central: categorias raiz --}}
                <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-gray-700 shrink-0">
                    <a href="/" class="hover:text-gray-900 transition-colors">Início</a>
                    @foreach (\App\Models\Category::whereNull('parent_id')->where('active', true)->orderBy('order')->get() as $cat)
                        <a href="{{ $cat->url }}" class="hover:text-gray-900 transition-colors">{{ $cat->name }}</a>
                    @endforeach
                </nav>

                {{-- Ações direita --}}
                <div class="flex items-center gap-3 shrink-0 ml-auto lg:ml-0">
                    @auth('customer')
                        <a href="{{ route('account.dashboard') }}"
                           class="hidden sm:flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            Minha Conta
                        </a>
                    @else
                        <a href="{{ route('account.login') }}"
                           class="hidden sm:flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            Entrar
                        </a>
                    @endauth

                    {{-- Ícone do carrinho --}}
                    @livewire('shop.cart-icon')
                </div>

            </div>
        </div>
    </header>

    {{-- Slot full-width (banners, hero) — fora do container --}}
    @hasSection('hero')
        @yield('hero')
    @endif

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ═══ Pre-footer: Newsletter ═══ --}}
    <section class="bg-gray-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-7">
                <h2 class="text-2xl font-bold text-white">Fique por dentro das novidades</h2>
                <p class="mt-2 text-indigo-200 text-sm">Cadastre-se e receba promoções exclusivas, lançamentos e dicas em primeira mão.</p>
            </div>
            @livewire('shop.newsletter-form')
        </div>
    </section>

    {{-- ═══ Footer ═══ --}}
    <footer class="bg-gray-50 text-gray-900">

        {{-- Bloco principal --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

                {{-- Coluna 1: Logo + Info da loja --}}
                <div class="lg:col-span-1">
                    {{-- Logo --}}
                    <a href="/" class="inline-block mb-4">
                        @if ($logoFooterUrl)
                            <img src="{{ $logoFooterUrl }}" alt="{{ $storeName }}" class="h-9 w-auto">
                        @else
                            <span class="text-xl font-bold text-white">{{ $storeName }}</span>
                        @endif
                    </a>

                    @if ($storeSlogan)
                        <p class="text-sm text-gray-400 mb-5">{{ $storeSlogan }}</p>
                    @endif

                    {{-- Endereço --}}
                    @if ($storeAddress)
                        <div class="flex gap-2.5 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <span class="text-sm text-gray-400 leading-relaxed">{{ $storeAddress }}</span>
                        </div>
                    @endif

                    {{-- Horário --}}
                    @if ($storeHours)
                        <div class="flex gap-2.5 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span class="text-sm text-gray-400">{{ $storeHours }}</span>
                        </div>
                    @endif

                    {{-- Redes sociais --}}
                    @php
                        $socials = array_filter([
                            'Instagram'  => ['url' => $socialInstagram,  'icon' => 'instagram'],
                            'Facebook'   => ['url' => $socialFacebook,   'icon' => 'facebook'],
                            'YouTube'    => ['url' => $socialYoutube,    'icon' => 'youtube'],
                            'TikTok'     => ['url' => $socialTiktok,     'icon' => 'tiktok'],
                            'Pinterest'  => ['url' => $socialPinterest,  'icon' => 'pinterest'],
                            'LinkedIn'   => ['url' => $socialLinkedin,   'icon' => 'linkedin'],
                            'X/Twitter'  => ['url' => $socialTwitter,    'icon' => 'twitter'],
                        ], fn($s) => !empty($s['url']));
                    @endphp
                    @if (!empty($socials))
                        <div class="flex items-center gap-3 mt-5">
                            @foreach ($socials as $name => $social)
                                <a href="{{ $social['url'] }}"
                                   target="_blank" rel="noopener noreferrer"
                                   title="{{ $name }}"
                                   class="w-8 h-8 rounded-full bg-gray-800 hover:bg-indigo-600 flex items-center justify-center transition-colors">
                                    @if ($social['icon'] === 'instagram')
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069Zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073Zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881Z"/></svg>
                                    @elseif ($social['icon'] === 'facebook')
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073Z"/></svg>
                                    @elseif ($social['icon'] === 'youtube')
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814ZM9.545 15.568V8.432L15.818 12l-6.273 3.568Z"/></svg>
                                    @elseif ($social['icon'] === 'tiktok')
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07Z"/></svg>
                                    @elseif ($social['icon'] === 'pinterest')
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 0 1 .083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0Z"/></svg>
                                    @elseif ($social['icon'] === 'linkedin')
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286ZM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065Zm1.782 13.019H3.555V9h3.564v11.452ZM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003Z"/></svg>
                                    @elseif ($social['icon'] === 'twitter')
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Coluna 2: Contato --}}
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-widest mb-5">Contato</h4>
                    <ul class="space-y-3">
                        @if ($storePhone)
                            <li>
                                <a href="tel:{{ preg_replace('/\D/', '', $storePhone) }}"
                                   class="flex items-start gap-2.5 text-sm text-gray-400 hover:text-white transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6v.75Z" />
                                    </svg>
                                    {{ $storePhone }}
                                </a>
                            </li>
                        @endif
                        @if ($storeWhatsapp)
                            <li>
                                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $storeWhatsapp) }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="flex items-start gap-2.5 text-sm text-gray-400 hover:text-white transition-colors">
                                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                                    </svg>
                                    {{ $storeWhatsapp }}
                                </a>
                            </li>
                        @endif
                        @if ($storeEmail)
                            <li>
                                <a href="mailto:{{ $storeEmail }}"
                                   class="flex items-start gap-2.5 text-sm text-gray-400 hover:text-white transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                    </svg>
                                    {{ $storeEmail }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- Coluna 3: Institucional (páginas dinâmicas) --}}
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-widest mb-5">Institucional</h4>
                    <ul class="space-y-2.5">
                        @forelse ($storePages as $page)
                            <li>
                                <a href="/pagina/{{ $page->slug }}"
                                   class="text-sm text-gray-400 hover:text-white transition-colors">
                                    {{ $page->title }}
                                </a>
                            </li>
                        @empty
                            <li class="text-sm text-gray-600 italic">—</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Coluna 4: Minha Conta --}}
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-widest mb-5">Minha Conta</h4>
                    <ul class="space-y-2.5">
                        @auth('customer')
                            <li>
                                <a href="{{ route('account.dashboard') }}" class="text-sm text-gray-400 hover:text-white transition-colors">
                                    Painel
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('account.orders.index') }}" class="text-sm text-gray-400 hover:text-white transition-colors">
                                    Meus Pedidos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('account.profile.edit') }}" class="text-sm text-gray-400 hover:text-white transition-colors">
                                    Meus Dados
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('account.addresses.index') }}" class="text-sm text-gray-400 hover:text-white transition-colors">
                                    Meus Endereços
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('account.login') }}" class="text-sm text-gray-400 hover:text-white transition-colors">
                                    Entrar
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('account.register') }}" class="text-sm text-gray-400 hover:text-white transition-colors">
                                    Criar conta
                                </a>
                            </li>
                        @endauth
                    </ul>
                </div>

            </div>
        </div>

        {{-- Selos de confiança --}}
        @if ($sealPaymentUrl || $sealSecurityUrl || $sealTransportUrl)
            <div class="border-t border-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-wrap items-center justify-center gap-6">
                        @if ($sealPaymentUrl)
                            <img src="{{ $sealPaymentUrl }}" alt="Formas de pagamento" class="h-8 w-auto opacity-70 hover:opacity-100 transition-opacity">
                        @endif
                        @if ($sealSecurityUrl)
                            <img src="{{ $sealSecurityUrl }}" alt="Site seguro" class="h-8 w-auto opacity-70 hover:opacity-100 transition-opacity">
                        @endif
                        @if ($sealTransportUrl)
                            <img src="{{ $sealTransportUrl }}" alt="Transportadoras" class="h-8 w-auto opacity-70 hover:opacity-100 transition-opacity">
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Rodapé inferior --}}
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
                    <p>
                        &copy; {{ date('Y') }} {{ $storeName }}. Todos os direitos reservados.
                        @if ($storeRazaoSocial)
                            &nbsp;·&nbsp; {{ $storeRazaoSocial }}
                        @endif
                        @if ($storeCnpj)
                            &nbsp;·&nbsp; CNPJ {{ $storeCnpj }}
                        @endif
                    </p>
                    <p>Desenvolvido por <span class="text-gray-400">Targos</span></p>
                </div>
            </div>
        </div>

    </footer>

    @livewireScripts
    @stack('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    if (status === 419) {
                        preventDefault();
                        if (confirm('Sua sessão expirou. Deseja recarregar a página para continuar?')) {
                            window.location.reload();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
