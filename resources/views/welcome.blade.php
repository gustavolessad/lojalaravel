<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                {{-- Logo --}}
                <a href="/" class="text-xl font-bold text-gray-900">
                    {{ config('app.name') }}
                </a>

                {{-- Nav central: categorias raiz --}}
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-700">
                    <a href="/" class="hover:text-gray-900">Início</a>
                    @foreach (\App\Models\Category::whereNull('parent_id')->where('active', true)->orderBy('order')->get() as $cat)
                        <a href="{{ $cat->url }}" class="hover:text-gray-900">{{ $cat->name }}</a>
                    @endforeach
                </nav>

                {{-- Ações direita --}}
                <div class="flex items-center gap-4">
                    @auth('customer')
                        <a href="{{ route('account.dashboard') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">
                            Minha Conta
                        </a>
                    @else
                        <a href="{{ route('account.login') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">
                            Entrar
                        </a>
                    @endauth

                    {{-- Ícone do carrinho --}}
                    @livewire('shop.cart-icon')
                </div>

            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        seja bem vindo

    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
