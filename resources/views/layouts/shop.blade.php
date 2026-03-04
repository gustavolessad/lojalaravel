@php
use App\Models\Setting;
use App\Models\StorePage;
use Datlechin\FilamentMenuBuilder\Models\Menu;
use Illuminate\Support\Facades\Storage;

$storeName = Setting::get('store_name', config('app.name'));
$storeSlogan = Setting::get('store_slogan', '');

// SEO global
$seoDefaultTitle       = Setting::get('seo_meta_title', $storeName);
$seoDefaultDescription = Setting::get('seo_meta_description', '');
$seoGoogleAnalytics    = Setting::get('seo_google_analytics', '');
$seoGoogleTagManager   = Setting::get('seo_google_tag_manager', '');
$storeLogo = Setting::get('store_logo', '');
$storeLogoFooter = Setting::get('store_logo_footer', '');
$storePhone = Setting::get('store_phone', '');
$storeWhatsapp = Setting::get('store_whatsapp', '');
$storeEmail = Setting::get('store_email', '');
$storeAddress = Setting::get('store_address', '');
$storeHours = Setting::get('store_hours', '');
$storeCnpj = Setting::get('store_cpf_cnpj', '');
$storeRazaoSocial = Setting::get('store_razao_social', '');

$socialInstagram = Setting::get('social_instagram', '');
$socialFacebook = Setting::get('social_facebook', '');
$socialYoutube = Setting::get('social_youtube', '');
$socialTiktok = Setting::get('social_tiktok', '');
$socialPinterest = Setting::get('social_pinterest', '');
$socialLinkedin = Setting::get('social_linkedin', '');
$socialTwitter = Setting::get('social_twitter', '');

$sealPayment = Setting::get('seal_payment', '');
$sealSecurity = Setting::get('seal_security', '');
$sealTransport = Setting::get('seal_transport', '');

$logoUrl = $storeLogo ? Storage::disk('public')->url($storeLogo) : null;
$logoFooterUrl = $storeLogoFooter ? Storage::disk('public')->url($storeLogoFooter) : $logoUrl;

$sealPaymentUrl = $sealPayment ? Storage::disk('public')->url($sealPayment) : null;
$sealSecurityUrl = $sealSecurity ? Storage::disk('public')->url($sealSecurity) : null;
$sealTransportUrl = $sealTransport ? Storage::disk('public')->url($sealTransport) : null;

$storePages = StorePage::orderBy('title')->get();
$principalMenu = Menu::location('principal');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ── Título ─────────────────────────────────────────────────────────── --}}
    @hasSection('title')
        <title>@yield('title') — {{ $storeName }}</title>
    @else
        <title>{{ $seoDefaultTitle ?: $storeName }}</title>
    @endif

    {{-- ── Meta robots (noindex para páginas privadas) ─────────────────────── --}}
    @hasSection('meta_robots')
        <meta name="robots" content="@yield('meta_robots')">
    @endif

    {{-- ── Meta descrição e keywords ──────────────────────────────────────── --}}
    @if ($seoDefaultDescription || View::hasSection('meta_description'))
        <meta name="description" content="@yield('meta_description', $seoDefaultDescription)">
    @endif
    @hasSection('meta_keywords')
        <meta name="keywords" content="@yield('meta_keywords')">
    @endif

    {{-- ── Canonical ──────────────────────────────────────────────────────── --}}
    <link rel="canonical" href="@yield('canonical', request()->url())">

    {{-- ── Open Graph (padrão da loja, páginas substituem com @section) ───── --}}
    @hasSection('og_tags')
        @yield('og_tags')
    @else
        <meta property="og:type"        content="website">
        <meta property="og:site_name"   content="{{ $storeName }}">
        <meta property="og:title"       content="{{ $seoDefaultTitle ?: $storeName }}">
        <meta property="og:description" content="{{ $seoDefaultDescription }}">
        <meta property="og:url"         content="{{ request()->url() }}">
        @if ($logoUrl)
            <meta property="og:image" content="{{ $logoUrl }}">
        @endif
        <meta name="twitter:card"        content="summary">
        <meta name="twitter:site"        content="{{ $storeName }}">
        <meta name="twitter:title"       content="{{ $seoDefaultTitle ?: $storeName }}">
        <meta name="twitter:description" content="{{ $seoDefaultDescription }}">
    @endif

    {{-- ── Google Tag Manager ─────────────────────────────────────────────── --}}
    @if ($seoGoogleTagManager)
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{{ $seoGoogleTagManager }}');</script>
    @endif

    {{-- ── Google Analytics (GA4 sem GTM) ───────────────────────────────── --}}
    @if ($seoGoogleAnalytics && !$seoGoogleTagManager)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seoGoogleAnalytics }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $seoGoogleAnalytics }}');</script>
    @endif

    {{-- ── JSON-LD global: Organization + WebSite ─────────────────────────── --}}
    @php
        $sameAs = array_values(array_filter([
            $socialInstagram, $socialFacebook, $socialYoutube,
            $socialTiktok, $socialPinterest, $socialLinkedin, $socialTwitter,
        ]));
        $orgData = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => $storeName,
            'url'      => url('/'),
        ];
        if ($logoUrl) $orgData['logo'] = $logoUrl;
        if ($storeEmail) $orgData['email'] = $storeEmail;
        if ($storePhone) $orgData['telephone'] = $storePhone;
        if ($storeAddress) $orgData['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $storeAddress];
        if (! empty($sameAs)) $orgData['sameAs'] = $sameAs;

        $siteData = [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => $storeName,
            'url'             => url('/'),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => url('/busca') . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($orgData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($siteData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

    {{-- ── Dados estruturados e head extras (JSON-LD, etc.) ───────────────── --}}
    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @livewireScriptConfig
    <style>
        [x-cloak] {
            display: none !important;
        }

        @media (max-width: 1023px) {
            .drawer-init-hidden {
                display: none !important;
            }
        }
    </style>
</head>

<body class="text-gray-900 antialiased">

    {{-- Google Tag Manager (noscript) --}}
    @if ($seoGoogleTagManager)
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $seoGoogleTagManager }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    <div id="headerTop" class="bg-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 text-xs text-white font-medium flex justify-between items-center">
            <div class="mensagem-boas-vindas">
                Seja bem vindo a {{ $storeName }}
            </div>
            <div class="redes-sociais flex items-center gap-3">
                Siga nas redes
                <div class="instagram">
                    <a href="{{ $socialInstagram }}" target="_blank">
                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069Zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073Zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881Z" />
                        </svg>
                    </a>
                </div>

                <div class="facebook">
                    <a href="{{ $socialFacebook }}" target="_blank">
                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073Z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Header ═══ --}}
    <header class="bg-white" x-data="{ menuOpen: false }"
        x-init="$watch('menuOpen', v => document.body.classList.toggle('overflow-hidden', v))">

        {{-- Overlay mobile --}}
        <div x-show="menuOpen"
            @click="menuOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/40 z-40 lg:hidden"
            style="display:none"></div>

        {{-- Drawer de menu mobile --}}
        <div x-cloak
            class="fixed top-0 left-0 bottom-0 w-72 bg-white z-50 flex flex-col lg:hidden
                    transition-transform duration-300 ease-in-out overflow-hidden"
            :class="menuOpen ? 'translate-x-0 shadow-xl' : '-translate-x-full'">

            {{-- Cabeçalho do drawer: boas-vindas --}}
            <div class="flex items-center justify-between px-4 py-3 shrink-0 mb-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        @auth('customer')
                        <p class="text-xs text-gray-400">Minha conta</p>
                        <a href="{{ route('account.dashboard') }}" @click="menuOpen = false"
                            class="text-sm font-semibold text-gray-900 hover:text-gray-600 transition-colors leading-none">
                            Olá, {{ explode(' ', auth('customer')->user()->name)[0] }}
                        </a>
                        @else
                        <p class="text-xs text-gray-400 leading-none mb-1">Bem-vindo(a)</p>
                        <div class="flex items-center gap-1.5 leading-none">
                            <a href="{{ route('account.login') }}" @click="menuOpen = false"
                                class="text-sm font-semibold text-gray-900 hover:text-gray-600 transition-colors">Entrar</a>
                            <span class="text-gray-300 text-sm font-normal">·</span>
                            <a href="{{ route('account.register') }}" @click="menuOpen = false"
                                class="text-sm font-semibold text-gray-900 hover:text-gray-600 transition-colors">Cadastrar</a>
                        </div>
                        @endauth
                    </div>
                </div>
                <button @click="menuOpen = false"
                    class="p-1.5 text-gray-400 hover:text-gray-900 transition-colors rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Busca --}}
            <div class="px-4 shrink-0">
                @livewire('shop.search-bar')
            </div>

            {{-- Itens de menu --}}
            <nav class="flex-1 overflow-y-auto py-2">
                @if ($principalMenu && $principalMenu->menuItems->isNotEmpty())
                @foreach ($principalMenu->menuItems as $item)
                @php
                $hasChildren = $item->children->isNotEmpty();
                $itemUrl = $item->url;
                $itemTarget = ($item->target && $item->target->value !== '_self') ? $item->target->value : null;
                $itemTitle = $item->linkable
                ? ($item->linkable->name ?? $item->linkable->title ?? $item->title)
                : $item->title;
                @endphp

                @if ($hasChildren)
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center justify-between w-full px-5 py-3 text-sm font-semibold text-gray-800 hover:text-black hover:bg-gray-50 transition-colors">
                        <span>{{ $itemTitle }}</span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="open && 'rotate-180'"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="bg-gray-50 border-y border-gray-100">
                        @foreach ($item->children as $child)
                        @php
                        $childTarget = ($child->target && $child->target->value !== '_self') ? $child->target->value : null;
                        $childTitle = $child->linkable
                        ? ($child->linkable->name ?? $child->linkable->title ?? $child->title)
                        : $child->title;
                        @endphp
                        <a href="{{ $child->url ?? '#' }}"
                            @if ($childTarget) target="{{ $childTarget }}" @endif
                            @click="menuOpen = false"
                            class="block pl-8 pr-5 py-2.5 text-sm text-gray-600 hover:text-black hover:bg-gray-100 transition-colors">
                            {{ $childTitle }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @elseif ($itemUrl)
                <a href="{{ $itemUrl }}"
                    @if ($itemTarget) target="{{ $itemTarget }}" @endif
                    @click="menuOpen = false"
                    class="block px-5 py-3 text-sm font-semibold text-gray-800 hover:text-black hover:bg-gray-50 transition-colors">
                    {{ $itemTitle }}
                </a>
                @else
                <span class="block px-5 py-3 text-sm font-semibold text-gray-400 select-none">{{ $itemTitle }}</span>
                @endif
                @endforeach
                @endif
            </nav>
        </div>

        {{-- Barra principal do header --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex items-center justify-between relative">

                {{-- Esquerda: Hamburguer (mobile) + Logo (desktop) --}}
                <div class="flex items-center">
                    {{-- Botão hamburguer (mobile only) --}}
                    <button @click="menuOpen = true"
                        class="lg:hidden p-2 -ml-2 text-gray-700 hover:text-black transition-colors rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    {{-- Logo (desktop, alinhado à esquerda) --}}
                    <a href="/" class="hidden lg:block shrink-0">
                        @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-10 w-auto">
                        @else
                        <span class="text-xl font-bold text-gray-900">{{ $storeName }}</span>
                        @endif
                    </a>
                </div>

                {{-- Centro: Logo centralizado (mobile) + Busca (desktop) --}}
                <a href="/" class="lg:hidden absolute left-1/2 -translate-x-1/2 shrink-0">
                    @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-10 w-auto">
                    @else
                    <span class="text-xl font-bold text-gray-900">{{ $storeName }}</span>
                    @endif
                </a>
                <div class="hidden flex-1 justify-center lg:flex">
                    @livewire('shop.search-bar')
                </div>

                {{-- Direita: Telefone (desktop) + Conta + Carrinho --}}
                <div class="flex items-center gap-3 lg:gap-4">
                    @if ($storePhone)
                    <div class="hidden lg:flex items-center gap-2.5 shrink-0 group">
                        <div class="w-10 h-10 text-gray-900 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6v.75Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 leading-none mb-1">Central de Atendimento</p>
                            <p class="text-sm font-semibold text-gray-900 leading-none group-hover:text-gray-600 transition-colors">{{ $storePhone }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Minha Conta --}}
                    <a href="{{ auth('customer')->check() ? route('account.dashboard') : route('account.login') }}"
                        class="hidden lg:flex items-center gap-1.5 text-sm transition-colors group">
                        <div class="flex items-center justify-center text-gray-900 w-10 h-10 bg-gray-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 leading-none mb-1">Minha conta</p>
                            <p class="text-sm font-semibold text-gray-900 leading-none group-hover:text-gray-600 transition-colors">
                                @auth('customer')
                                Olá, {{ explode(' ', auth('customer')->user()->name)[0] }}
                                @else
                                Entre ou Cadastre-se
                                @endauth
                            </p>
                        </div>
                    </a>

                    {{-- Ícone do carrinho --}}
                    @livewire('shop.cart-icon')
                </div>
            </div>
        </div>

        {{-- ─── Menu Principal (headerBottom — desktop only) ─── --}}
        @if ($principalMenu && $principalMenu->menuItems->isNotEmpty())
        <div id="headerBottom" class="bg-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav x-data="{ open: null }" class="hidden lg:flex items-center -mx-1">

                    @foreach ($principalMenu->menuItems as $item)
                    @php
                    $hasChildren = $item->children->isNotEmpty();
                    $itemUrl = $item->url;
                    $itemTarget = ($item->target && $item->target->value !== '_self') ? $item->target->value : null;
                    $itemTitle = $item->linkable
                    ? ($item->linkable->name ?? $item->linkable->title ?? $item->title)
                    : $item->title;
                    @endphp

                    @if ($hasChildren)
                    <div class="relative"
                        @mouseenter="open = {{ $item->id }}"
                        @mouseleave="open = null">

                        @if ($itemUrl)
                        <a href="{{ $itemUrl }}" @if ($itemTarget) target="{{ $itemTarget }}" @endif
                            class="flex items-center gap-1 px-3 py-3 font-medium text-gray-800 hover:text-black whitespace-nowrap transition-colors"
                            :class="{ 'text-black': open === {{ $item->id }} }">
                            {{ $itemTitle }}
                            <svg class="w-3 h-3 mt-px shrink-0 transition-transform duration-150"
                                :class="{ 'rotate-180': open === {{ $item->id }} }"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </a>
                        @else
                        <button type="button"
                            class="flex items-center gap-1 px-3 py-3 font-medium text-gray-800 hover:text-black whitespace-nowrap transition-colors"
                            :class="{ 'text-black': open === {{ $item->id }} }">
                            {{ $itemTitle }}
                            <svg class="w-3 h-3 mt-px shrink-0 transition-transform duration-150"
                                :class="{ 'rotate-180': open === {{ $item->id }} }"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        @endif

                        <div x-show="open === {{ $item->id }}"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="absolute left-0 top-full z-50 min-w-48 bg-white rounded-b-lg shadow-lg border overflow-hidden border-gray-100"
                            style="display:none">
                            @foreach ($item->children as $child)
                            @php
                            $childTarget = ($child->target && $child->target->value !== '_self') ? $child->target->value : null;
                            $childTitle = $child->linkable
                            ? ($child->linkable->name ?? $child->linkable->title ?? $child->title)
                            : $child->title;
                            @endphp
                            <a href="{{ $child->url ?? '#' }}"
                                @if ($childTarget) target="{{ $childTarget }}" @endif
                                class="block px-4 py-2.5 text-sm font-medium text-gray-800 hover:bg-gray-100 hover:text-black transition-colors whitespace-nowrap">
                                {{ $childTitle }}
                            </a>
                            @endforeach
                        </div>
                    </div>

                    @elseif ($itemUrl)
                    <a href="{{ $itemUrl }}"
                        @if ($itemTarget) target="{{ $itemTarget }}" @endif
                        class="px-3 py-3 font-medium text-gray-800 hover:text-black whitespace-nowrap transition-colors">
                        {{ $itemTitle }}
                    </a>

                    @else
                    <span class="px-3 py-3 font-medium text-gray-400 whitespace-nowrap select-none">
                        {{ $itemTitle }}
                    </span>
                    @endif

                    @endforeach

                </nav>
            </div>
        </div>
        @endif

    </header>

    {{-- Slot full-width (banners, hero) — fora do container --}}
    @hasSection('hero')
    @yield('hero')
    @endif

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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
    <section class="bg-black mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col lg:flex-row items-center gap-10">
            <div class="text-center lg:text-start flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 text-white rounded-full bg-green-700 flex items-center justify-center">
                    <svg class="w-6 h-6 " fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.99 14.993 6-6m6 3.001c0 1.268-.63 2.39-1.593 3.069a3.746 3.746 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043 3.745 3.745 0 0 1-3.068 1.593c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 0 1-3.296-1.043 3.746 3.746 0 0 1-1.043-3.297 3.746 3.746 0 0 1-1.593-3.068c0-1.268.63-2.39 1.593-3.068a3.746 3.746 0 0 1 1.043-3.297 3.745 3.745 0 0 1 3.296-1.042 3.745 3.745 0 0 1 3.068-1.594c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.297 3.746 3.746 0 0 1 1.593 3.068ZM9.74 9.743h.008v.007H9.74v-.007Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Ganhe 10%OFF na primeira compra!</h2>
                    <p class="text-gray-300 text-xs">Cadastre-se em nossa newsletter e receba seu cupom por e-mail</p>
                </div>
            </div>
            @livewire('shop.newsletter-form')
        </div>
    </section>

    {{-- ═══ Footer ═══ --}}
    <footer class="text-gray-900">

        {{-- Bloco principal --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14">
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
                    <p class="text-sm text-gray-700 mb-5">{{ $storeSlogan }}</p>
                    @endif

                    {{-- Endereço --}}
                    @if ($storeAddress)
                    <div class="flex gap-2.5 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span class="text-sm text-gray-700 font-medium leading-relaxed">{{ $storeAddress }}</span>
                    </div>
                    @endif

                    {{-- Horário --}}
                    @if ($storeHours)
                    <div class="flex gap-2.5 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">{{ $storeHours }}</span>
                    </div>
                    @endif

                    {{-- Redes sociais --}}
                    @php
                    $socials = array_filter([
                    'Instagram' => ['url' => $socialInstagram, 'icon' => 'instagram'],
                    'Facebook' => ['url' => $socialFacebook, 'icon' => 'facebook'],
                    'YouTube' => ['url' => $socialYoutube, 'icon' => 'youtube'],
                    'TikTok' => ['url' => $socialTiktok, 'icon' => 'tiktok'],
                    'Pinterest' => ['url' => $socialPinterest, 'icon' => 'pinterest'],
                    'LinkedIn' => ['url' => $socialLinkedin, 'icon' => 'linkedin'],
                    'X/Twitter' => ['url' => $socialTwitter, 'icon' => 'twitter'],
                    ], fn($s) => !empty($s['url']));
                    @endphp
                    @if (!empty($socials))
                    <div class="flex items-center gap-3 mt-5">
                        @foreach ($socials as $name => $social)
                        <a href="{{ $social['url'] }}"
                            target="_blank" rel="noopener noreferrer"
                            title="{{ $name }}"
                            class="w-8 h-8 rounded-full bg-gray-800 hover:bg-green-700 flex items-center justify-center transition-colors">
                            @if ($social['icon'] === 'instagram')
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069Zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073Zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881Z" />
                            </svg>
                            @elseif ($social['icon'] === 'facebook')
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073Z" />
                            </svg>
                            @elseif ($social['icon'] === 'youtube')
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814ZM9.545 15.568V8.432L15.818 12l-6.273 3.568Z" />
                            </svg>
                            @elseif ($social['icon'] === 'tiktok')
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07Z" />
                            </svg>
                            @elseif ($social['icon'] === 'pinterest')
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 0 1 .083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0Z" />
                            </svg>
                            @elseif ($social['icon'] === 'linkedin')
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286ZM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065Zm1.782 13.019H3.555V9h3.564v11.452ZM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003Z" />
                            </svg>
                            @elseif ($social['icon'] === 'twitter')
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                            @endif
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Coluna 2: Contato --}}
                <div>
                    <h4 class="font-semibold text-black mb-5">Contato</h4>
                    <ul class="space-y-3 font-medium">
                        @if ($storePhone)
                        <li>
                            <a href="tel:{{ preg_replace('/\D/', '', $storePhone) }}"
                                class="flex items-start gap-2.5 text-sm text-gray-600 hover:text-gray-900 transition-colors">
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
                                class="flex items-start gap-2.5 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
                                </svg>
                                {{ $storeWhatsapp }}
                            </a>
                        </li>
                        @endif
                        @if ($storeEmail)
                        <li>
                            <a href="mailto:{{ $storeEmail }}"
                                class="flex items-start gap-2.5 text-sm text-gray-600 hover:text-gray-900 transition-colors">
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
                    <h4 class="font-semibold text-black mb-5">Institucional</h4>
                    <ul class="space-y-2.5 font-medium">
                        @forelse ($storePages as $page)
                        <li>
                            <a href="/pagina/{{ $page->slug }}"
                                class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
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
                    <h4 class="font-semibold text-black mb-5">Minha Conta</h4>
                    <ul class="space-y-2.5 font-medium">
                        @auth('customer')
                        <li>
                            <a href="{{ route('account.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                Painel
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                Meus Pedidos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                Meus Dados
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.addresses.index') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                Meus Endereços
                            </a>
                        </li>
                        @else
                        <li>
                            <a href="{{ route('account.login') }}" class="text-sm text-gray-600 hover:text-gray-900  transition-colors">
                                Entrar
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.register') }}" class="text-sm text-gray-600 hover:text-gray-900  transition-colors">
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

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-wrap items-center justify-around gap-10">
                @if ($sealPaymentUrl)
                <div class="selo-pagamentos text-center">
                    <p class="text-sm text-gray-900 font-medium mb-3">Pagamentos</p>
                    <img src="{{ $sealPaymentUrl }}" alt="Formas de pagamento" class="h-12 w-auto">
                </div>
                @endif
                @if ($sealSecurityUrl)
                <div class="selo-seguranca text-center">
                    <p class="text-sm text-gray-900 font-medium mb-3">Segurança</p>
                    <img src="{{ $sealSecurityUrl }}" alt="Site seguro" class="h-12 w-auto">
                </div>
                @endif
                @if ($sealTransportUrl)
                <div class="selo-transportes text-center">
                    <p class="text-sm text-gray-900 font-medium mb-3">Métodos de envio</p>
                    <img src="{{ $sealTransportUrl }}" alt="Transportadoras" class="h-12 w-auto">
                </div>
                @endif
            </div>
        </div>

        @endif

        {{-- Rodapé inferior --}}
        <div class="text-xs text-gray-500 text-center space-y-1 py-10 border-t border-gray-100">
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
            <p class="text-center text-xs text-gray-600 flex flex-col items-center gap-1 mt-6"><span>Desenvolvido por</span>
                <a href="https://targos.com.br"  target="_blank"><img class="h-4 w-auto" src="{{ asset('images/logo-targos.svg') }}" alt="Desenvolvido por Targos"></a>
            </p>
        </div>

    </footer>

    @stack('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({
                fail
            }) => {
                fail(({
                    status,
                    preventDefault
                }) => {
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