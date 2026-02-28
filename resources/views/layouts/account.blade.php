<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Minha Conta') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900 antialiased min-h-screen">

    {{-- Top Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="text-xl font-bold text-gray-900 tracking-tight">
                    {{ config('app.name') }}
                </a>
                <a href="/" class="hidden sm:flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                    </svg>
                    Continuar comprando
                </a>
            </div>
        </div>
    </header>

    {{-- Mobile Nav (horizontal scroll) --}}
    @auth('customer')
    <nav class="lg:hidden bg-white border-b border-gray-200 overflow-x-auto">
        <div class="flex items-center gap-1 px-4 py-2 min-w-max">
            <a href="{{ route('account.dashboard') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                      {{ request()->routeIs('account.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Início
            </a>
            <a href="{{ route('account.orders.index') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                      {{ request()->routeIs('account.orders.*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                </svg>
                Pedidos
            </a>
            <a href="{{ route('account.profile.edit') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                      {{ request()->routeIs('account.profile.*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Meus Dados
            </a>
            <a href="{{ route('account.addresses.index') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                      {{ request()->routeIs('account.addresses.*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                Endereços
            </a>
            <a href="{{ route('account.returns.index') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                      {{ request()->routeIs('account.returns.*') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Trocas
            </a>
        </div>
    </nav>
    @endauth

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
        <div class="flex gap-6 lg:gap-8 items-start">

            {{-- Sidebar (desktop only) --}}
            @auth('customer')
            @php $customer = auth('customer')->user(); @endphp
            <aside class="hidden lg:flex flex-col w-64 shrink-0 gap-2">

                {{-- Welcome Card --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-gray-900 text-white flex items-center justify-center text-lg font-bold shrink-0 select-none">
                            {{ mb_strtoupper(mb_substr($customer->display_name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Olá,</p>
                            <p class="font-semibold text-gray-900 truncate leading-tight">{{ $customer->display_name }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">Bem-vindo(a) de volta ao seu painel.</p>
                </div>

                {{-- Navigation --}}
                <nav class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="p-2">
                        <p class="px-3 py-1.5 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Menu</p>

                        <a href="{{ route('account.dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group
                                  {{ request()->routeIs('account.dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <span class="w-5 h-5 flex items-center justify-center shrink-0
                                         {{ request()->routeIs('account.dashboard') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                            </span>
                            Início
                        </a>

                        <a href="{{ route('account.orders.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group
                                  {{ request()->routeIs('account.orders.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <span class="w-5 h-5 flex items-center justify-center shrink-0
                                         {{ request()->routeIs('account.orders.*') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                                </svg>
                            </span>
                            Meus Pedidos
                        </a>

                        <a href="{{ route('account.profile.edit') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group
                                  {{ request()->routeIs('account.profile.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <span class="w-5 h-5 flex items-center justify-center shrink-0
                                         {{ request()->routeIs('account.profile.*') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </span>
                            Meus Dados
                        </a>

                        <a href="{{ route('account.addresses.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group
                                  {{ request()->routeIs('account.addresses.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <span class="w-5 h-5 flex items-center justify-center shrink-0
                                         {{ request()->routeIs('account.addresses.*') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            </span>
                            Meus Endereços
                        </a>

                        <a href="{{ route('account.returns.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group
                                  {{ request()->routeIs('account.returns.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <span class="w-5 h-5 flex items-center justify-center shrink-0
                                         {{ request()->routeIs('account.returns.*') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-600' }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </span>
                            Trocas e Devoluções
                        </a>
                    </div>

                    <div class="border-t border-gray-100 p-2">
                        <form method="POST" action="{{ route('account.logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 hover:bg-red-50 hover:text-red-600 transition-all group">
                                <span class="w-5 h-5 flex items-center justify-center shrink-0 text-gray-400 group-hover:text-red-500">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                </span>
                                Sair
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>
            @endauth

            {{-- Main Content --}}
            <main class="flex-1 min-w-0">
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>

        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
