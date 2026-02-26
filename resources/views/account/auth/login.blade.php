@extends('layouts.auth')

@section('title', 'Entrar')
@section('heading', 'Acesse sua conta')

@section('content')
    <form method="POST" action="{{ route('account.login') }}" class="space-y-5">
        @csrf

        {{-- E-mail --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                E-mail
            </label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('email') border-red-400 @enderror"
                placeholder="seu@email.com"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Senha --}}
        <div>
            <div class="flex justify-between items-center mb-1">
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Senha
                </label>
                <a href="#" class="text-xs text-gray-500 hover:text-gray-900">Esqueceu a senha?</a>
            </div>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('password') border-red-400 @enderror"
                placeholder="••••••••"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lembrar --}}
        <div class="flex items-center">
            <input
                type="checkbox"
                id="remember"
                name="remember"
                class="h-4 w-4 text-gray-900 border-gray-300 rounded"
            >
            <label for="remember" class="ml-2 text-sm text-gray-600">
                Lembrar de mim
            </label>
        </div>

        <button
            type="submit"
            class="w-full bg-gray-900 text-white py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
        >
            Entrar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Não tem cadastro?
        <a href="{{ route('account.register') }}" class="font-medium text-gray-900 hover:underline">
            Criar conta
        </a>
    </p>
@endsection
