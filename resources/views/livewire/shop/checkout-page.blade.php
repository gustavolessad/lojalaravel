@push('header-steps')
{{-- Steps inline no header — Alpine reage a eventos Livewire 'checkout-step-changed' --}}
<div
    x-data="{ step: {{ $step }} }"
    x-on:checkout-step-changed.window="step = $event.detail.step"
    x-show="step > 0"
    class="select-none">

    {{-- Desktop: círculos + labels + conectores curtos --}}
    <div class="hidden sm:flex items-center">

        <div class="flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-full flex items-center justify-center font-bold flex-shrink-0 transition-colors"
                 :class="step >= 1 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                <svg x-show="step > 1" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-show="step <= 1" class="text-[10px]">1</span>
            </div>
            <span class="text-xs font-medium transition-colors"
                  :class="step >= 1 ? 'text-green-700' : 'text-gray-400'">Endereço</span>
        </div>

        <div class="w-5 h-px mx-2 transition-colors" :class="step > 1 ? 'bg-green-400' : 'bg-gray-300'"></div>

        <div class="flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-full flex items-center justify-center font-bold flex-shrink-0 transition-colors"
                 :class="step >= 2 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                <svg x-show="step > 2" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-show="step <= 2" class="text-[10px]">2</span>
            </div>
            <span class="text-xs font-medium transition-colors"
                  :class="step >= 2 ? 'text-green-700' : 'text-gray-400'">Frete</span>
        </div>

        <div class="w-5 h-px mx-2 transition-colors" :class="step > 2 ? 'bg-green-400' : 'bg-gray-300'"></div>

        <div class="flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-full flex items-center justify-center font-bold flex-shrink-0 transition-colors"
                 :class="step >= 3 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                <svg x-show="step > 3" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-show="step <= 3" class="text-[10px]">3</span>
            </div>
            <span class="text-xs font-medium transition-colors"
                  :class="step >= 3 ? 'text-green-700' : 'text-gray-400'">Pagamento</span>
        </div>

        <div class="w-5 h-px mx-2 transition-colors" :class="step > 3 ? 'bg-green-400' : 'bg-gray-300'"></div>

        <div class="flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-full flex items-center justify-center font-bold flex-shrink-0 transition-colors"
                 :class="step >= 4 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                <span class="text-[10px]">4</span>
            </div>
            <span class="text-xs font-medium transition-colors"
                  :class="step >= 4 ? 'text-green-700' : 'text-gray-400'">Revisão</span>
        </div>

    </div>

    {{-- Mobile: 4 dots com conector --}}
    <div class="flex sm:hidden items-center gap-1">
        <div class="w-2 h-2 rounded-full transition-colors" :class="step >= 1 ? 'bg-green-600' : 'bg-gray-300'"></div>
        <div class="w-3 h-px transition-colors" :class="step > 1 ? 'bg-green-400' : 'bg-gray-300'"></div>
        <div class="w-2 h-2 rounded-full transition-colors" :class="step >= 2 ? 'bg-green-600' : 'bg-gray-300'"></div>
        <div class="w-3 h-px transition-colors" :class="step > 2 ? 'bg-green-400' : 'bg-gray-300'"></div>
        <div class="w-2 h-2 rounded-full transition-colors" :class="step >= 3 ? 'bg-green-600' : 'bg-gray-300'"></div>
        <div class="w-3 h-px transition-colors" :class="step > 3 ? 'bg-green-400' : 'bg-gray-300'"></div>
        <div class="w-2 h-2 rounded-full transition-colors" :class="step >= 4 ? 'bg-green-600' : 'bg-gray-300'"></div>
    </div>

</div>
@endpush

<div>

    {{-- ── Layout principal ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- ════════════════════════════════════════════════════════════════
             COLUNA ESQUERDA: formulários por etapa
        ════════════════════════════════════════════════════════════════ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- ── Mensagem de erro global ───────────────────────── --}}
            @if ($errorMessage)
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    {{ $errorMessage }}
                </div>
            @endif

            {{-- ══════════════════════════════════════════════════════
                 ETAPA 0 — Identificação (Login / Cadastro)
            ══════════════════════════════════════════════════════ --}}
            @if ($step === 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">

                    <h2 class="text-base font-semibold text-gray-900">Identificação</h2>

                    {{-- Abas Login / Cadastro --}}
                    <div class="flex border border-gray-200 rounded-xl overflow-hidden">
                        <button
                            type="button"
                            wire:click="switchAuthMode('login')"
                            class="flex-1 py-2 text-sm font-medium transition-colors
                                {{ $authMode === 'login'
                                    ? 'bg-gray-900 text-white'
                                    : 'bg-white text-gray-600 hover:bg-gray-50' }}"
                        >
                            Entrar na conta
                        </button>
                        <button
                            type="button"
                            wire:click="switchAuthMode('register')"
                            class="flex-1 py-2 text-sm font-medium transition-colors
                                {{ $authMode === 'register'
                                    ? 'bg-gray-900 text-white'
                                    : 'bg-white text-gray-600 hover:bg-gray-50' }}"
                        >
                            Criar conta
                        </button>
                    </div>

                    {{-- ── LOGIN ── --}}
                    @if ($authMode === 'login')
                        <form wire:submit.prevent="attemptLogin" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">E-mail *</label>
                                <input wire:model="loginEmail" type="email" placeholder="seu@email.com" autocomplete="email"
                                       class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('loginEmail') border-red-400 @enderror">
                                @error('loginEmail') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Senha *</label>
                                <input wire:model="loginPassword" type="password" placeholder="••••••••" autocomplete="current-password"
                                       class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('loginPassword') border-red-400 @enderror">
                                @error('loginPassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="w-full py-3 px-6 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800 transition-colors disabled:opacity-60">
                                <span wire:loading.remove wire:target="attemptLogin" class="flex items-center justify-center gap-2">
                                    Entrar e continuar
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                                <span wire:loading wire:target="attemptLogin">Verificando...</span>
                            </button>
                            <p class="text-center text-xs text-gray-500">
                                Ainda não tem uma conta?
                                <button type="button" wire:click="switchAuthMode('register')" class="text-gray-900 font-medium hover:underline">Crie a sua</button>
                            </p>
                        </form>

                    {{-- ── CADASTRO ── --}}
                    @else
                        <form wire:submit.prevent="attemptRegister" class="space-y-3">

                            {{-- Tipo de cadastro — controlado pelo Livewire (sem Alpine) --}}
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center justify-center gap-2 py-2 px-4 rounded-xl border cursor-pointer transition-colors
                                    {{ $registerType === 'pf' ? 'border-gray-900 bg-gray-50 text-gray-900' : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                                    <input type="radio" wire:model.live="registerType" value="pf" class="sr-only">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Pessoa Física</span>
                                </label>
                                <label class="flex items-center justify-center gap-2 py-2 px-4 rounded-xl border cursor-pointer transition-colors
                                    {{ $registerType === 'pj' ? 'border-gray-900 bg-gray-50 text-gray-900' : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                                    <input type="radio" wire:model.live="registerType" value="pj" class="sr-only">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6h1.5m-1.5 3h1.5m-1.5 3h1.5M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                                    </svg>
                                    <span class="text-sm font-medium">Pessoa Jurídica</span>
                                </label>
                            </div>

                            {{-- Campos Pessoa Física --}}
                            @if ($registerType === 'pf')
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nome completo *</label>
                                        <input wire:model="registerName" type="text" placeholder="Seu nome completo" autocomplete="name"
                                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('registerName') border-red-400 @enderror">
                                        @error('registerName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">CPF</label>
                                            <input wire:model="registerCpf" type="text" placeholder="000.000.000-00"
                                                   class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('registerCpf') border-red-400 @enderror">
                                            @error('registerCpf') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Data de nascimento</label>
                                            <input wire:model="registerBirthDate" type="date"
                                                   class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            @else
                            {{-- Campos Pessoa Jurídica --}}
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Razão Social *</label>
                                        <input wire:model="registerCompanyName" type="text" placeholder="Nome da empresa"
                                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('registerCompanyName') border-red-400 @enderror">
                                        @error('registerCompanyName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">CNPJ</label>
                                            <input wire:model="registerCnpj" type="text" placeholder="00.000.000/0000-00"
                                                   class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('registerCnpj') border-red-400 @enderror">
                                            @error('registerCnpj') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Nome do responsável *</label>
                                            <input wire:model="registerResponsibleName" type="text" placeholder="Nome completo"
                                                   class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('registerResponsibleName') border-red-400 @enderror">
                                            @error('registerResponsibleName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Campos comuns --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">E-mail *</label>
                                    <input wire:model="registerEmail" type="email" placeholder="seu@email.com" autocomplete="email"
                                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('registerEmail') border-red-400 @enderror">
                                    @error('registerEmail') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Celular</label>
                                    <input wire:model="registerMobile" type="tel" placeholder="(11) 99999-9999" autocomplete="tel"
                                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Senha *</label>
                                    <input wire:model="registerPassword" type="password" placeholder="Mín. 8 caracteres" autocomplete="new-password"
                                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('registerPassword') border-red-400 @enderror">
                                    @error('registerPassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Confirmar senha *</label>
                                    <input wire:model="registerPasswordConfirmation" type="password" placeholder="Repita a senha" autocomplete="new-password"
                                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 focus:outline-none @error('registerPasswordConfirmation') border-red-400 @enderror">
                                    @error('registerPasswordConfirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="w-full py-3 px-6 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800 transition-colors disabled:opacity-60">
                                <span wire:loading.remove wire:target="attemptRegister" class="flex items-center justify-center gap-2">
                                    Criar conta e continuar
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                                <span wire:loading wire:target="attemptRegister">Criando conta...</span>
                            </button>
                        </form>
                    @endif

                </div>

            {{-- ══════════════════════════════════════════════════════
                 ETAPA 1 — Endereço
            ══════════════════════════════════════════════════════ --}}
            @elseif ($step === 1)
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">

                    {{-- Info do cliente logado --}}
                    @if ($this->customer)
                        <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-100 rounded-xl">
                            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-green-700">
                                Olá, <span class="font-semibold">{{ $this->customer->display_name }}</span>! ({{ $this->customer->email }})
                            </p>
                        </div>
                    @endif

                    <h2 class="text-base font-semibold text-gray-900">Endereço de entrega</h2>

                    {{-- Seletor de endereços salvos --}}
                    @if ($this->savedAddresses->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($this->savedAddresses as $addr)
                                <div class="flex items-start gap-3 p-3 border rounded-xl transition-all
                                    {{ $selectedAddressId === $addr->id && ! $useNewAddress
                                        ? 'border-indigo-500 bg-indigo-50'
                                        : 'border-gray-200 hover:border-gray-300' }}">
                                    <input
                                        type="radio"
                                        name="saved_address"
                                        wire:click="selectSavedAddress({{ $addr->id }})"
                                        @checked($selectedAddressId === $addr->id && ! $useNewAddress)
                                        class="mt-1 accent-indigo-600 cursor-pointer flex-shrink-0"
                                    >
                                    <div class="text-sm leading-snug flex-1 cursor-pointer" wire:click="selectSavedAddress({{ $addr->id }})">
                                        @if ($addr->label)
                                            <span class="font-medium text-gray-900">{{ $addr->label }} — </span>
                                        @endif
                                        {{ $addr->full_address }}
                                    </div>
                                    <button type="button"
                                            wire:click="editSavedAddress({{ $addr->id }})"
                                            class="text-xs text-indigo-500 hover:text-indigo-700 flex-shrink-0 underline">
                                        Editar
                                    </button>
                                </div>
                            @endforeach

                            <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all
                                {{ $useNewAddress && ! $editingAddressId ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input
                                    type="radio"
                                    name="saved_address"
                                    wire:click="switchToNewAddress"
                                    @checked($useNewAddress && ! $editingAddressId)
                                    class="accent-indigo-600"
                                >
                                <span class="text-sm font-medium text-gray-700">Usar outro endereço</span>
                            </label>
                        </div>
                    @endif

                    {{-- Formulário de endereço --}}
                    @if ($useNewAddress || $this->savedAddresses->isEmpty())

                        {{-- Indicador de modo de edição --}}
                        @if ($editingAddressId)
                            <div class="flex items-center justify-between px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg">
                                <span class="text-xs text-amber-700 font-medium">Editando endereço salvo</span>
                                <button type="button" wire:click="selectSavedAddress({{ $editingAddressId }})"
                                        class="text-xs text-amber-600 hover:text-amber-800 underline">
                                    Cancelar
                                </button>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            {{-- Identificação do endereço --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Identificação do endereço</label>
                                <input wire:model="addrLabel" type="text" placeholder="Ex: Casa, Trabalho, Minha empresa..."
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                                <p class="text-xs text-gray-400 mt-1">Opcional — ajuda a identificar o endereço nas próximas compras</p>
                            </div>

                            {{-- Nome do destinatário --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nome do destinatário *</label>
                                <input wire:model="addrName" type="text" placeholder="Quem vai receber"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('addrName') border-red-400 @enderror">
                                @error('addrName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- CEP com lookup --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">CEP *</label>
                                <input wire:model.blur="addrZip" type="text" placeholder="00000-000" maxlength="9"
                                       wire:change="lookupZip"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('addrZip') border-red-400 @enderror">
                                @error('addrZip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Telefone --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Telefone</label>
                                <input wire:model="addrPhone" type="tel" placeholder="(11) 99999-9999"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                            </div>

                            {{-- Rua --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Logradouro *</label>
                                <input wire:model="addrStreet" type="text" placeholder="Rua, Avenida, etc."
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('addrStreet') border-red-400 @enderror">
                                @error('addrStreet') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Número + Complemento --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Número *</label>
                                <input wire:model="addrNumber" type="text" placeholder="123"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('addrNumber') border-red-400 @enderror">
                                @error('addrNumber') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Complemento</label>
                                <input wire:model="addrComplement" type="text" placeholder="Apto, Bloco..."
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                            </div>

                            {{-- Bairro --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Bairro *</label>
                                <input wire:model="addrDistrict" type="text"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('addrDistrict') border-red-400 @enderror">
                                @error('addrDistrict') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Cidade --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Cidade *</label>
                                <input wire:model="addrCity" type="text"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('addrCity') border-red-400 @enderror">
                                @error('addrCity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Estado --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Estado *</label>
                                <select wire:model="addrState"
                                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none bg-white @error('addrState') border-red-400 @enderror">
                                    <option value="">UF</option>
                                    @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                        <option value="{{ $uf }}">{{ $uf }}</option>
                                    @endforeach
                                </select>
                                @error('addrState') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="pt-2">
                        <button wire:click="goToShipping" wire:loading.attr="disabled"
                                class="w-full py-3 px-6 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800 transition-colors disabled:opacity-60">
                            <span wire:loading.remove wire:target="goToShipping" class="flex items-center justify-center gap-2">
                                Continuar para o Frete
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </span>
                            <span wire:loading wire:target="goToShipping">Calculando...</span>
                        </button>
                    </div>
                </div>

            {{-- ══════════════════════════════════════════════════════
                 ETAPA 2 — Frete
            ══════════════════════════════════════════════════════ --}}
            @elseif ($step === 2)
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4"
                     wire:init="loadShipping">
                    <div class="flex items-center gap-3">
                        <h2 class="text-base font-semibold text-gray-900 flex-1">Opções de entrega</h2>
                        <button type="button" wire:click="backTo(1)" class="text-sm text-indigo-600 hover:text-indigo-800">← Endereço</button>
                    </div>

                    <p class="text-sm text-gray-500">
                        Entrega para: <span class="font-medium text-gray-700">{{ $addrCity }}/{{ strtoupper($addrState) }}</span>
                        — CEP {{ $addrZip }}
                    </p>

                    {{-- Loading skeleton --}}
                    @if ($loadingShipping)
                        <div class="space-y-3">
                            @foreach ([1, 2, 3] as $_)
                                <div class="animate-pulse flex items-center gap-4 p-4 border border-gray-200 rounded-xl">
                                    <div class="w-4 h-4 bg-gray-200 rounded-full flex-shrink-0"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-3 bg-gray-200 rounded w-2/5"></div>
                                        <div class="h-2 bg-gray-100 rounded w-1/4"></div>
                                    </div>
                                    <div class="h-4 bg-gray-200 rounded w-14"></div>
                                </div>
                            @endforeach
                        </div>

                    {{-- Sem resultados --}}
                    @elseif (empty($shippingOptions))
                        <p class="text-sm text-red-600 py-2">
                            Não foi possível calcular o frete para este CEP.
                            <button type="button" wire:click="backTo(1)" class="underline">Verifique o endereço</button> e tente novamente.
                        </p>

                    {{-- Opções disponíveis --}}
                    @else
                        <div class="space-y-3">
                            @foreach ($shippingOptions as $idx => $option)
                                <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all
                                    {{ $selectedShippingIndex === $idx
                                        ? 'border-indigo-500 bg-indigo-50'
                                        : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio"
                                           name="shipping_option"
                                           wire:click="$set('selectedShippingIndex', {{ $idx }})"
                                           @checked($selectedShippingIndex === $idx)
                                           class="accent-indigo-600">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ trim(($option['company'] ?? '') . ' ' . $option['name']) }}
                                        </p>
                                        <p class="text-xs text-gray-500">Prazo estimado: {{ $option['days'] }} {{ $option['days'] === 1 ? 'dia útil' : 'dias úteis' }}</p>
                                    </div>
                                    <span class="text-sm font-bold {{ $option['price'] == 0 ? 'text-emerald-600' : 'text-gray-900' }}">
                                        {{ $option['price'] == 0 ? 'Grátis' : 'R$ ' . number_format($option['price'], 2, ',', '.') }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    @error('selectedShippingIndex')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <button wire:click="goToPayment"
                            @disabled($loadingShipping || empty($shippingOptions))
                            class="w-full flex items-center justify-center gap-2 py-3 px-6 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Continuar para o Pagamento
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>

            {{-- ══════════════════════════════════════════════════════
                 ETAPA 3 — Pagamento
            ══════════════════════════════════════════════════════ --}}
            @elseif ($step === 3)
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
                    <div class="flex items-center gap-3">
                        <h2 class="text-base font-semibold text-gray-900 flex-1">Forma de pagamento</h2>
                        <button type="button" wire:click="backTo(2)" class="text-sm text-indigo-600 hover:text-indigo-800">← Frete</button>
                    </div>

                    {{-- Seletor de método --}}
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all
                            {{ $paymentMethod === 'pix' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" name="payment_method"
                                   wire:click="$set('paymentMethod','pix')"
                                   @checked($paymentMethod === 'pix')
                                   class="accent-indigo-600">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">PIX</p>
                                <p class="text-xs text-gray-500">Aprovação imediata</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all
                            {{ $paymentMethod === 'credit_card' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" name="payment_method"
                                   wire:click="$set('paymentMethod','credit_card')"
                                   @checked($paymentMethod === 'credit_card')
                                   class="accent-indigo-600">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Cartão de crédito</p>
                                <p class="text-xs text-gray-500">Em até {{ app(\App\Services\PaymentCalculator::class)->installmentsMax() }}×</p>
                            </div>
                        </label>
                    </div>

                    {{-- Formulário cartão --}}
                    @if ($paymentMethod === 'credit_card')
                        <div class="space-y-4 pt-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nome no cartão *</label>
                                <input wire:model="cardHolder" type="text" placeholder="Igual ao cartão" autocomplete="cc-name"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('cardHolder') border-red-400 @enderror">
                                @error('cardHolder') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Número do cartão *</label>
                                <input wire:model="cardNumber" type="text" placeholder="0000 0000 0000 0000" maxlength="19" autocomplete="cc-number"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('cardNumber') border-red-400 @enderror">
                                @error('cardNumber') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Validade *</label>
                                    <input wire:model="cardExpiry" type="text" placeholder="MM/AA" maxlength="5" autocomplete="cc-exp"
                                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('cardExpiry') border-red-400 @enderror">
                                    @error('cardExpiry') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">CVV *</label>
                                    <input wire:model="cardCvv" type="text" placeholder="000" maxlength="4" autocomplete="cc-csc"
                                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none @error('cardCvv') border-red-400 @enderror">
                                    @error('cardCvv') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Parcelamento</label>
                                <select wire:model="installments"
                                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none bg-white">
                                    @foreach ($this->installmentOptions as $opt)
                                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        {{-- Desconto PIX --}}
                        @if ($this->pixTotal)
                            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-green-800">
                                        Pagando no PIX:
                                        <span class="text-lg">R$ {{ number_format($this->pixTotal, 2, ',', '.') }}</span>
                                    </p>
                                    <p class="text-xs text-green-600 mt-0.5">
                                        Você economiza R$ {{ number_format($this->pixSavings, 2, ',', '.') }} pagando à vista
                                    </p>
                                </div>
                            </div>
                        @endif
                        {{-- Info QR Code --}}
                        <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-blue-700">O QR Code PIX será gerado após a confirmação do pedido.</p>
                        </div>
                    @endif

                    <button wire:click="goToReview"
                            class="w-full flex items-center justify-center gap-2 py-3 px-6 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800 transition-colors">
                        Revisar pedido
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>

            {{-- ══════════════════════════════════════════════════════
                 ETAPA 4 — Revisão
            ══════════════════════════════════════════════════════ --}}
            @elseif ($step === 4)
                <div class="space-y-4">

                    {{-- Resumo do endereço --}}
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-900">Entrega</h3>
                            <button type="button" wire:click="backTo(1)" class="text-xs text-indigo-600 hover:text-indigo-800">Alterar</button>
                        </div>
                        <p class="text-sm text-gray-700">{{ $addrName }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $addrStreet }}, {{ $addrNumber }}
                            @if($addrComplement) — {{ $addrComplement }} @endif
                        </p>
                        <p class="text-sm text-gray-500">{{ $addrDistrict }}, {{ $addrCity }}/{{ strtoupper($addrState) }} — {{ $addrZip }}</p>
                    </div>

                    {{-- Frete + Pagamento lado a lado --}}
                    <div class="grid grid-cols-2 gap-4">

                        {{-- Resumo do frete --}}
                        <div class="bg-white rounded-2xl border border-gray-200 p-5">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-semibold text-gray-900">Frete</h3>
                                <button type="button" wire:click="backTo(2)" class="text-xs text-indigo-600 hover:text-indigo-800">Alterar</button>
                            </div>
                            @if ($this->selectedShipping)
                                <p class="text-sm text-gray-700">
                                    {{ trim(($this->selectedShipping['company'] ?? '') . ' ' . $this->selectedShipping['name']) }}
                                </p>
                                <p class="text-sm mt-0.5">
                                    <span class="{{ $this->selectedShipping['price'] == 0 ? 'text-emerald-600 font-semibold' : 'text-gray-700' }}">
                                        {{ $this->selectedShipping['price'] == 0 ? 'Grátis' : 'R$ ' . number_format($this->selectedShipping['price'], 2, ',', '.') }}
                                    </span>
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $this->selectedShipping['days'] }} dias úteis</p>
                            @endif
                        </div>

                        {{-- Resumo do pagamento --}}
                        <div class="bg-white rounded-2xl border border-gray-200 p-5">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-semibold text-gray-900">Pagamento</h3>
                                <button type="button" wire:click="backTo(3)" class="text-xs text-indigo-600 hover:text-indigo-800">Alterar</button>
                            </div>
                            <p class="text-sm text-gray-700">
                                {{ $paymentMethod === 'pix' ? 'PIX' : 'Cartão de crédito' }}
                            </p>
                            @if ($paymentMethod === 'credit_card' && $cardNumber)
                                <p class="text-xs text-gray-500 mt-0.5">
                                    **** {{ substr(preg_replace('/\D/', '', $cardNumber), -4) }}
                                    @if ($installments > 1)
                                        · {{ $installments }}x sem juros
                                    @endif
                                </p>
                            @elseif ($paymentMethod === 'pix')
                                <p class="text-xs text-gray-400 mt-0.5">Aprovação imediata</p>
                            @endif
                        </div>

                    </div>

                    {{-- Observações (accordion) --}}
                    <div x-data="{ open: @js((bool) $notes) }"
                        class="bg-white rounded-2xl border transition-colors"
                        :class="open ? 'border-gray-300' : 'border-gray-200'">
                        <button type="button" @click="open = !open"
                            class="w-full flex items-center gap-2.5 px-5 py-3 text-sm font-medium text-gray-700 text-left">
                            <svg class="w-4 h-4 shrink-0 transition-colors" :class="open ? 'text-gray-900' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                            <span class="flex-1 truncate">Observações</span>
                            @if ($notes)
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-gray-300 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="px-5 pb-4 border-t border-gray-100 pt-3">
                            <textarea wire:model="notes" rows="2" placeholder="Instruções de entrega, referências, etc."
                                      class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent focus:outline-none resize-none transition"></textarea>
                        </div>
                    </div>

                    {{-- Botão confirmar --}}
                    <button
                        wire:click="placeOrder"
                        wire:loading.attr="disabled"
                        wire:target="placeOrder"
                        class="w-full py-4 px-6 bg-green-700 text-white text-base font-bold rounded-2xl hover:bg-green-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="placeOrder">
                            Confirmar Pedido — R$ {{ number_format($this->total, 2, ',', '.') }}
                        </span>
                        <span wire:loading wire:target="placeOrder" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Processando...
                        </span>
                    </button>
                </div>
            @endif

        </div>{{-- /coluna esquerda --}}

        {{-- ════════════════════════════════════════════════════════════════
             COLUNA DIREITA: resumo do pedido (sticky)
        ════════════════════════════════════════════════════════════════ --}}
        <div class="lg:sticky lg:top-24 space-y-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">
                    Resumo do pedido
                    <span class="text-gray-400 font-normal">({{ $this->cart->item_count }} {{ $this->cart->item_count === 1 ? 'item' : 'itens' }})</span>
                </h3>

                <div class="space-y-3 mb-4">
                    @foreach ($this->cart->items as $item)
                        <div class="flex gap-3 items-center">
                            <div class="w-10 h-10 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-900 truncate">{{ $item->product->name }}</p>
                                @if ($item->variant)
                                    <p class="text-xs text-gray-500">{{ $item->variant->label }}</p>
                                @endif
                                <p class="text-xs text-gray-500">Qtd: {{ $item->quantity }}</p>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 flex-shrink-0">
                                R$ {{ number_format($item->subtotal, 2, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 pt-3 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>R$ {{ number_format($this->cart->subtotal, 2, ',', '.') }}</span>
                    </div>

                    @if ($this->cart->coupon_discount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Cupom ({{ $this->cart->coupon_code }})</span>
                            <span>− R$ {{ number_format($this->cart->coupon_discount, 2, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-gray-600">
                        <span>Frete</span>
                        @if ($this->selectedShipping)
                            <span class="{{ $this->selectedShipping['price'] == 0 ? 'text-emerald-600' : '' }}">
                                {{ $this->selectedShipping['price'] == 0 ? 'Grátis' : 'R$ ' . number_format($this->selectedShipping['price'], 2, ',', '.') }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </div>
                    <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-100 text-base">
                        <span>Total</span>
                        <span>R$ {{ number_format($this->total, 2, ',', '.') }}</span>
                    </div>

                    {{-- Hints PIX / Parcelamento --}}
                    @php
                        $calc      = app(\App\Services\PaymentCalculator::class);
                        $pixHint   = $calc->pixPrice($this->total);
                        $instHint  = $calc->bestFreeInstallmentLabel($this->total);
                    @endphp
                    @if ($pixHint || $instHint)
                        <div class="pt-3 border-t border-gray-100 space-y-1">
                            @if ($pixHint)
                                <p class="text-sm text-green-700 text-end">
                                    ou <span class="font-semibold">R$ {{ number_format($pixHint, 2, ',', '.') }}</span> no PIX
                                </p>
                            @endif
                            @if ($instHint)
                                <p class="text-xs text-gray-500 text-end">ou {{ $instHint }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /grid --}}
</div>
