@extends('layouts.auth')

@section('title', 'Criar conta')
@section('heading', 'Crie sua conta')

@section('form')
    <form method="POST" action="{{ route('account.register') }}" class="space-y-5" x-data="{ type: '{{ old('type', 'pf') }}' }">
        @csrf

        {{-- Tipo de pessoa --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de cadastro</label>
            <div class="grid grid-cols-2 gap-3">
                <label
                    class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg border-2 cursor-pointer transition-colors"
                    :class="type === 'pf' ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 text-gray-600 hover:border-gray-400'"
                >
                    <input type="radio" name="type" value="pf" x-model="type" class="sr-only">
                    <span class="text-sm font-medium">Pessoa Física</span>
                </label>
                <label
                    class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg border-2 cursor-pointer transition-colors"
                    :class="type === 'pj' ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 text-gray-600 hover:border-gray-400'"
                >
                    <input type="radio" name="type" value="pj" x-model="type" class="sr-only">
                    <span class="text-sm font-medium">Pessoa Jurídica</span>
                </label>
            </div>
            @error('type')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nome --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Nome completo
            </label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('name') border-red-400 @enderror"
                placeholder="Seu nome completo">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- CPF (Pessoa Física) --}}
        <div x-show="type === 'pf'" x-transition>
            <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
            <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}"
                x-bind:required="type === 'pf'"
                x-mask="999.999.999-99"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('cpf') border-red-400 @enderror"
                placeholder="000.000.000-00">
            @error('cpf')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Data de nascimento (Pessoa Física) --}}
        <div x-show="type === 'pf'" x-transition>
            <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">
                Data de nascimento
            </label>
            <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('birth_date') border-red-400 @enderror">
            @error('birth_date')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Razão Social (Pessoa Jurídica) --}}
        <div x-show="type === 'pj'" x-transition>
            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                Razão Social
            </label>
            <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}"
                x-bind:required="type === 'pj'"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('company_name') border-red-400 @enderror"
                placeholder="Nome da empresa">
            @error('company_name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- CNPJ (Pessoa Jurídica) --}}
        <div x-show="type === 'pj'" x-transition>
            <label for="cnpj" class="block text-sm font-medium text-gray-700 mb-1">CNPJ</label>
            <input type="text" id="cnpj" name="cnpj" value="{{ old('cnpj') }}"
                x-bind:required="type === 'pj'"
                x-mask="99.999.999/9999-99"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('cnpj') border-red-400 @enderror"
                placeholder="00.000.000/0000-00">
            @error('cnpj')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nome do Responsável (Pessoa Jurídica) --}}
        <div x-show="type === 'pj'" x-transition>
            <label for="responsible_name" class="block text-sm font-medium text-gray-700 mb-1">
                Nome do responsável
            </label>
            <input type="text" id="responsible_name" name="responsible_name" value="{{ old('responsible_name') }}"
                x-bind:required="type === 'pj'"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('responsible_name') border-red-400 @enderror"
                placeholder="Nome completo do responsável">
            @error('responsible_name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- E-mail --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('email') border-red-400 @enderror"
                placeholder="seu@email.com">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Celular --}}
        <div>
            <label for="mobile" class="block text-sm font-medium text-gray-700 mb-1">
                Celular
            </label>
            <input type="tel" id="mobile" name="mobile" value="{{ old('mobile') }}"
                x-mask="(99) 99999-9999"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                placeholder="(00) 00000-0000">
        </div>

        {{-- Senha --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
            <input type="password" id="password" name="password" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('password') border-red-400 @enderror"
                placeholder="Mínimo 8 caracteres">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirmar Senha --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                Confirmar senha
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                placeholder="Repita a senha">
        </div>

        <button
            type="submit"
            class="w-full bg-gray-900 text-white py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
        >
            Criar conta
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Já tem conta?
        <a href="{{ route('account.login') }}" class="font-medium text-gray-900 hover:underline">
            Entrar
        </a>
    </p>
@endsection
