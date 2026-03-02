@extends('layouts.auth')

@section('title', 'Entrar')
@section('heading', 'Acesse sua conta')

@section('icon')
    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
    </svg>
@endsection

@section('form')
    <form method="POST" action="{{ route('account.login') }}" class="space-y-4">
        @csrf

        {{-- E-mail --}}
        <div>
            <label for="email" class="block text-xs font-medium text-gray-700 mb-1.5">
                E-mail
            </label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('email') border-red-400 @enderror"
                placeholder="seu@email.com"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Senha --}}
        <div>
            <div class="flex justify-between items-center mb-1.5">
                <label for="password" class="block text-xs font-medium text-gray-700">
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
                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('password') border-red-400 @enderror"
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
            class="w-full bg-gray-900 text-white py-2.5 px-5 rounded-xl text-sm font-medium hover:bg-gray-700 transition-colors"
        >
            Entrar
        </button>
    </form>

    <div class="mt-5 pt-4 border-t border-gray-100 text-center">
        <p class="text-sm text-gray-500">
            Não tem cadastro?
            <a href="{{ route('account.register') }}" class="font-medium text-gray-900 hover:underline">
                Criar conta
            </a>
        </p>
    </div>
@endsection
