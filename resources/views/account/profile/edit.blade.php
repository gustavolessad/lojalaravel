@extends('layouts.account')

@section('title', 'Meus Dados')

@section('page-content')

<div class="mb-5">
    <h1 class="font-medium text-gray-900">Meus Dados</h1>
    <p class="text-sm text-gray-500 mt-0.5">Gerencie suas informações pessoais e de acesso</p>
</div>

<div class="space-y-4">

    {{-- Dados pessoais --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-900">
                {{ $customer->isPF() ? 'Dados Pessoais' : 'Dados da Empresa' }}
            </h2>
        </div>

        <form action="{{ route('account.profile.update') }}" method="POST" class="p-5">
            @csrf
            @method('PUT')

            @if ($customer->isPF())
                {{-- Pessoa Física --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Nome completo</label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('name') border-red-400 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">CPF</label>
                        <input type="text" value="{{ $customer->cpf }}" readonly
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-500 cursor-not-allowed">
                        <p class="mt-1 text-xs text-gray-400">CPF não pode ser alterado</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">RG <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <input type="text" name="rg" value="{{ old('rg', $customer->rg) }}"
                               placeholder="00.000.000-0"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('rg') border-red-400 @enderror">
                        @error('rg') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">E-mail</label>
                        <input type="email" value="{{ $customer->email }}" readonly
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-500 cursor-not-allowed">
                        <p class="mt-1 text-xs text-gray-400">E-mail não pode ser alterado</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Data de nascimento</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('birth_date') border-red-400 @enderror">
                        @error('birth_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Celular</label>
                        <input type="tel" name="mobile" value="{{ old('mobile', $customer->mobile) }}"
                               placeholder="(00) 00000-0000"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('mobile') border-red-400 @enderror">
                        @error('mobile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Telefone fixo <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <input type="tel" name="phone" value="{{ old('phone', $customer->phone) }}"
                               placeholder="(00) 0000-0000"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('phone') border-red-400 @enderror">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

            @else
                {{-- Pessoa Jurídica --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Razão social</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('company_name') border-red-400 @enderror">
                        @error('company_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">CNPJ</label>
                        <input type="text" value="{{ $customer->cnpj }}" readonly
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-500 cursor-not-allowed">
                        <p class="mt-1 text-xs text-gray-400">CNPJ não pode ser alterado</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Inscrição estadual <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <input type="text" name="state_registration" value="{{ old('state_registration', $customer->state_registration) }}"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('state_registration') border-red-400 @enderror">
                        @error('state_registration') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Responsável</label>
                        <input type="text" name="responsible_name" value="{{ old('responsible_name', $customer->responsible_name) }}"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('responsible_name') border-red-400 @enderror">
                        @error('responsible_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">E-mail</label>
                        <input type="email" value="{{ $customer->email }}" readonly
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-500 cursor-not-allowed">
                        <p class="mt-1 text-xs text-gray-400">E-mail não pode ser alterado</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Celular</label>
                        <input type="tel" name="mobile" value="{{ old('mobile', $customer->mobile) }}"
                               placeholder="(00) 00000-0000"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('mobile') border-red-400 @enderror">
                        @error('mobile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Telefone fixo <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <input type="tel" name="phone" value="{{ old('phone', $customer->phone) }}"
                               placeholder="(00) 0000-0000"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('phone') border-red-400 @enderror">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            <div class="mt-5 flex justify-end">
                <button type="submit"
                        class="bg-gray-900 text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-gray-700 transition-colors">
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>

    {{-- Alterar senha --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-900">Alterar Senha</h2>
        </div>

        <form action="{{ route('account.profile.password') }}" method="POST" class="p-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Senha atual</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('current_password') border-red-400 @enderror">
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Nova senha</label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('password') border-red-400 @enderror">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Confirmar nova senha</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                </div>
            </div>

            <p class="text-xs text-gray-400 mt-3">A senha deve ter pelo menos 8 caracteres.</p>

            <div class="mt-4 flex justify-end">
                <button type="submit"
                        class="bg-gray-900 text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-gray-700 transition-colors">
                    Alterar senha
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
