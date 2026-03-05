<div>

    {{-- ── Steps do checkout ─────────────────────────────────────────── --}}
    <div
        x-data="{ step: {{ $step }} }"
        x-on:checkout-step-changed.window="step = $event.detail.step"
        class="select-none mb-6">

        {{-- Desktop: ícones + labels + conectores --}}
        <div class="hidden sm:flex items-center justify-center">

            {{-- 0 — Identificação --}}
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                     :class="step >= 0 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                    <svg x-show="step > 0" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="step <= 0" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/>
                    </svg>
                </div>
                <span class="text-xs font-medium transition-colors"
                      :class="step >= 0 ? 'text-green-700' : 'text-gray-400'">Identificação</span>
            </div>

            <div class="w-5 h-px mx-2 transition-colors" :class="step > 0 ? 'bg-green-400' : 'bg-gray-300'"></div>

            {{-- 1 — Entrega --}}
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                     :class="step >= 1 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                    <svg x-show="step > 1" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="step <= 1" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                    </svg>
                </div>
                <span class="text-xs font-medium transition-colors"
                      :class="step >= 1 ? 'text-green-700' : 'text-gray-400'">Entrega</span>
            </div>

            <div class="w-5 h-px mx-2 transition-colors" :class="step > 1 ? 'bg-green-400' : 'bg-gray-300'"></div>

            {{-- 2 — Pagamento --}}
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                     :class="step >= 2 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                    <svg x-show="step > 2" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="step <= 2" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium transition-colors"
                      :class="step >= 2 ? 'text-green-700' : 'text-gray-400'">Pagamento</span>
            </div>

            <div class="w-5 h-px mx-2 transition-colors" :class="step > 2 ? 'bg-green-400' : 'bg-gray-300'"></div>

            {{-- 3 — Revisão --}}
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                     :class="step >= 3 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium transition-colors"
                      :class="step >= 3 ? 'text-green-700' : 'text-gray-400'">Revisão</span>
            </div>

        </div>

        {{-- Mobile: bolinhas com ícones + conectores --}}
        <div class="flex sm:hidden items-center justify-center gap-1">
            {{-- Identificação --}}
            <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                 :class="step >= 0 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                <svg x-show="step > 0" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="step <= 0" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/>
                </svg>
            </div>
            <div class="w-3 h-px transition-colors" :class="step > 0 ? 'bg-green-400' : 'bg-gray-300'"></div>

            {{-- Entrega --}}
            <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                 :class="step >= 1 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                <svg x-show="step > 1" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="step <= 1" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <div class="w-3 h-px transition-colors" :class="step > 1 ? 'bg-green-400' : 'bg-gray-300'"></div>

            {{-- Pagamento --}}
            <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                 :class="step >= 2 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                <svg x-show="step > 2" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="step <= 2" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                </svg>
            </div>
            <div class="w-3 h-px transition-colors" :class="step > 2 ? 'bg-green-400' : 'bg-gray-300'"></div>

            {{-- Revisão --}}
            <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                 :class="step >= 3 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-400'">
                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
        </div>

    </div>

    {{-- ── Layout principal ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

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

                    {{-- ── TELA 1: Digitar e-mail ── --}}
                    @if ($authStep === 'email')
                        <p class="text-sm text-gray-500">Informe seu e-mail para continuar. Se já tiver uma conta, pediremos sua senha. Caso contrário, criaremos uma para você.</p>

                        <form wire:submit.prevent="checkEmail" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">E-mail *</label>
                                <input wire:model="authEmail" type="email" placeholder="seu@email.com" autocomplete="email" autofocus
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('authEmail') border-red-400 @enderror">
                                @error('authEmail') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="w-full py-3 px-6 bg-green-700 text-white font-semibold rounded-xl hover:bg-green-800 transition-colors disabled:opacity-60">
                                <span wire:loading.remove wire:target="checkEmail" class="flex items-center justify-center gap-2">
                                    Continuar
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                                <span wire:loading wire:target="checkEmail">Verificando...</span>
                            </button>
                        </form>

                    {{-- ── TELA 2: Login (e-mail já cadastrado) ── --}}
                    @elseif ($authStep === 'login')
                        <div class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-xl">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                            </svg>
                            <span class="text-sm text-gray-700 flex-1">{{ $authEmail }}</span>
                            <button type="button" wire:click="backToEmailCheck" class="text-xs text-gray-500 hover:text-black underline flex-shrink-0">Alterar</button>
                        </div>

                        <form wire:submit.prevent="attemptLogin" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Senha *</label>
                                <input wire:model="loginPassword" type="password" placeholder="••••••••" autocomplete="current-password" autofocus
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('loginPassword') border-red-400 @enderror">
                                @error('loginPassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="w-full py-3 px-6 bg-green-700 text-white font-semibold rounded-xl hover:bg-green-800 transition-colors disabled:opacity-60">
                                <span wire:loading.remove wire:target="attemptLogin" class="flex items-center justify-center gap-2">
                                    Entrar e continuar
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                                <span wire:loading wire:target="attemptLogin">Verificando...</span>
                            </button>
                        </form>

                    {{-- ── TELA 3: Cadastro (e-mail novo) ── --}}
                    @elseif ($authStep === 'register')
                        <div class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-xl">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                            </svg>
                            <span class="text-sm text-gray-700 flex-1">{{ $authEmail }}</span>
                            <button type="button" wire:click="backToEmailCheck" class="text-xs text-gray-500 hover:text-black underline flex-shrink-0">Alterar</button>
                        </div>

                        <form wire:submit.prevent="attemptRegister" class="space-y-3">

                            {{-- Tipo de cadastro --}}
                            <div class="flex items-center gap-2">
                                <div wire:click="$set('registerType', 'pf')"
                                     class="flex items-center gap-2 px-3.5 py-2 border rounded-xl cursor-pointer transition-all
                                        {{ $registerType === 'pf' ? 'border-green-500 bg-green-50/60' : 'border-gray-200 hover:border-gray-300' }}">
                                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors
                                        {{ $registerType === 'pf' ? 'border-green-500 bg-green-500' : 'border-gray-300 bg-white' }}">
                                        @if ($registerType === 'pf')
                                            <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-700">Pessoa Física</span>
                                </div>
                                <div wire:click="$set('registerType', 'pj')"
                                     class="flex items-center gap-2 px-3.5 py-2 border rounded-xl cursor-pointer transition-all
                                        {{ $registerType === 'pj' ? 'border-green-500 bg-green-50/60' : 'border-gray-200 hover:border-gray-300' }}">
                                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors
                                        {{ $registerType === 'pj' ? 'border-green-500 bg-green-500' : 'border-gray-300 bg-white' }}">
                                        @if ($registerType === 'pj')
                                            <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-700">Pessoa Jurídica</span>
                                </div>
                            </div>

                            {{-- Campos Pessoa Física --}}
                            @if ($registerType === 'pf')
                                <div class="space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Nome completo *</label>
                                            <input wire:model="registerName" type="text" placeholder="Seu nome completo" autocomplete="name"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerName') border-red-400 @enderror">
                                            @error('registerName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">CPF *</label>
                                            <input wire:model="registerCpf" type="text" placeholder="000.000.000-00" required
                                                   x-mask="999.999.999-99"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerCpf') border-red-400 @enderror">
                                            @error('registerCpf')
                                                <p class="text-xs text-red-600 mt-1">
                                                    {{ $message }}
                                                    @if(str_contains($message, 'já está cadastrado'))
                                                        <button type="button" wire:click="backToEmailCheck" class="text-gray-900 font-medium underline hover:no-underline">Acesse sua conta</button>
                                                    @endif
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Data de nascimento *</label>
                                            <input wire:model="registerBirthDate" type="tel" required
                                                   placeholder="DD/MM/AAAA" x-mask="99/99/9999"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerBirthDate') border-red-400 @enderror">
                                            @error('registerBirthDate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Celular *</label>
                                            <input wire:model="registerMobile" type="tel" placeholder="(11) 99999-9999" autocomplete="tel" required
                                                   x-mask="(99) 99999-9999"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerMobile') border-red-400 @enderror">
                                            @error('registerMobile') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Senha *</label>
                                            <input wire:model="registerPassword" type="password" placeholder="Mín. 8 caracteres" autocomplete="new-password"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerPassword') border-red-400 @enderror">
                                            @error('registerPassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Confirmar senha *</label>
                                            <input wire:model="registerPasswordConfirmation" type="password" placeholder="Repita a senha" autocomplete="new-password"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerPasswordConfirmation') border-red-400 @enderror">
                                            @error('registerPasswordConfirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                            @else
                            {{-- Campos Pessoa Jurídica --}}
                                <div class="space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Razão Social *</label>
                                            <input wire:model="registerCompanyName" type="text" placeholder="Nome da empresa"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerCompanyName') border-red-400 @enderror">
                                            @error('registerCompanyName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">CNPJ *</label>
                                            <input wire:model="registerCnpj" type="text" placeholder="00.000.000/0000-00" required
                                                   x-mask="99.999.999/9999-99"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerCnpj') border-red-400 @enderror">
                                            @error('registerCnpj')
                                                <p class="text-xs text-red-600 mt-1">
                                                    {{ $message }}
                                                    @if(str_contains($message, 'já está cadastrado'))
                                                        <button type="button" wire:click="backToEmailCheck" class="text-gray-900 font-medium underline hover:no-underline">Acesse sua conta</button>
                                                    @endif
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Nome do responsável *</label>
                                            <input wire:model="registerResponsibleName" type="text" placeholder="Nome completo"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerResponsibleName') border-red-400 @enderror">
                                            @error('registerResponsibleName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Celular *</label>
                                            <input wire:model="registerMobile" type="tel" placeholder="(11) 99999-9999" autocomplete="tel" required
                                                   x-mask="(99) 99999-9999"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerMobile') border-red-400 @enderror">
                                            @error('registerMobile') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Senha *</label>
                                            <input wire:model="registerPassword" type="password" placeholder="Mín. 8 caracteres" autocomplete="new-password"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerPassword') border-red-400 @enderror">
                                            @error('registerPassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Confirmar senha *</label>
                                            <input wire:model="registerPasswordConfirmation" type="password" placeholder="Repita a senha" autocomplete="new-password"
                                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('registerPasswordConfirmation') border-red-400 @enderror">
                                            @error('registerPasswordConfirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <button type="submit" wire:loading.attr="disabled"
                                    class="w-full py-3 px-6 bg-green-700 text-white font-semibold rounded-xl hover:bg-green-800 transition-colors disabled:opacity-60">
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
                 ETAPA 1 — Entrega (endereço + frete)
            ══════════════════════════════════════════════════════ --}}
            @elseif ($step === 1)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">

                    {{-- Info do cliente logado --}}
                    @if ($this->customer)
                        <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-100 rounded-xl mb-4">
                            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-green-700">
                                Olá, <span class="font-semibold">{{ $this->customer->display_name }}</span>! ({{ $this->customer->email }})
                            </p>
                        </div>
                    @endif

                    <h3 class="text-sm font-semibold text-black mb-1">Entrega</h3>
                    <p class="text-xs text-gray-500 mb-4">Selecione um endereço de entrega</p>

                    {{-- Seletor de endereços salvos --}}
                    @if ($this->savedAddresses->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-3">
                            @foreach ($this->savedAddresses as $addr)
                                @if ($editingAddressId === $addr->id)
                                    {{-- ── Formulário de edição inline ── --}}
                                    <div class="sm:col-span-2 p-4 border-2 border-amber-400 bg-amber-50/30 rounded-xl space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-semibold text-amber-700">Editando endereço</span>
                                            <button type="button" wire:click="cancelEditAddress"
                                                    class="text-xs text-gray-500 hover:text-gray-700 underline">Cancelar</button>
                                        </div>

                                        @include('livewire.shop.partials.address-form')

                                        <button type="button" wire:click="saveEditedAddress" wire:loading.attr="disabled"
                                                class="w-full py-2.5 px-4 bg-gray-900 text-white text-sm font-semibold rounded-xl hover:bg-black transition-colors disabled:opacity-60">
                                            <span wire:loading.remove wire:target="saveEditedAddress">Salvar endereço</span>
                                            <span wire:loading wire:target="saveEditedAddress">Salvando...</span>
                                        </button>
                                    </div>
                                @else
                                    {{-- ── Card de endereço salvo ── --}}
                                    @php $isSelected = $selectedAddressId === $addr->id && ! $editingAddressId; @endphp
                                    <div wire:click="selectSavedAddress({{ $addr->id }})"
                                         class="relative p-3.5 border rounded-xl cursor-pointer transition-all
                                            {{ $isSelected ? 'border-green-500 bg-green-50/60' : 'border-gray-200 hover:border-gray-300' }}">
                                        {{-- Círculo no canto superior direito --}}
                                        <div class="absolute top-2.5 right-2.5 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors
                                            {{ $isSelected ? 'border-green-500 bg-green-500' : 'border-gray-300 bg-white' }}">
                                            @if ($isSelected)
                                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <p class="text-sm font-semibold text-gray-900 pr-7">{{ $addr->label ?: $this->customer->display_name }}</p>
                                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                            {{ $addr->street }}, {{ $addr->number }}@if($addr->complement) - {{ $addr->complement }}@endif, 
                                            {{ $addr->district }}, {{ $addr->city }}/{{ $addr->state }} - 
                                            CEP {{ $addr->cep }}
                                        </p>
                                        <button type="button"
                                                wire:click.stop="editSavedAddress({{ $addr->id }})"
                                                class="text-xs text-indigo-500 hover:text-indigo-700 underline mt-2">
                                            Editar
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if (! $editingAddressId)
                            <button type="button" wire:click="switchToNewAddress"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 underline">
                                + Usar outro endereço
                            </button>
                        @endif
                    @endif

                    {{-- Formulário de novo endereço --}}
                    @if (($useNewAddress && ! $editingAddressId) || $this->savedAddresses->isEmpty())
                        <div class="space-y-3">
                            @include('livewire.shop.partials.address-form')
                        </div>
                    @endif

                    @if (! $editingAddressId)
                        {{-- ── Opções de frete (inline) ────────────────────── --}}
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-black mb-1">Opções de frete</h3>
                            <p class="text-xs text-gray-500 mb-4">Selecione uma opção</p>

                        @if (! $addrZip || strlen(preg_replace('/\D/', '', $addrZip)) !== 8)
                            {{-- Aguardando CEP --}}
                            <div class="flex flex-col items-center justify-center py-6 text-gray-400">
                                <svg class="w-8 h-8 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                </svg>
                                <p class="text-sm">Preencha seu endereço acima para calcular o frete</p>
                            </div>
                        @elseif ($loadingShipping)
                            {{-- Skeleton de carregamento --}}
                            <div class="space-y-2 animate-pulse">
                                @for ($i = 0; $i < 3; $i++)
                                    <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl">
                                        <div class="w-4 h-4 bg-gray-200 rounded-full flex-shrink-0"></div>
                                        <div class="flex-1 space-y-1.5">
                                            <div class="h-3.5 bg-gray-200 rounded w-1/3"></div>
                                            <div class="h-3 bg-gray-100 rounded w-2/5"></div>
                                        </div>
                                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                                    </div>
                                @endfor
                            </div>
                        @else
                            {{-- Sem resultados --}}
                            @if (empty($shippingOptions))
                                <p class="text-sm text-red-600 py-2">
                                    Não foi possível calcular o frete para este CEP. Verifique o endereço e tente novamente.
                                </p>

                            {{-- Opções disponíveis --}}
                            @else
                                <div class="space-y-2">
                                    @foreach ($shippingOptions as $idx => $option)
                                        @php $isShipSelected = $selectedShippingIndex === $idx; @endphp
                                        <div wire:click="$set('selectedShippingIndex', {{ $idx }})"
                                             class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all
                                                {{ $isShipSelected ? 'border-green-500 bg-green-50/60' : 'border-gray-200 hover:border-gray-300' }}">
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors
                                                {{ $isShipSelected ? 'border-green-500 bg-green-500' : 'border-gray-300 bg-white' }}">
                                                @if ($isShipSelected)
                                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    {{ trim(($option['company'] ?? '') . ' ' . $option['name']) }}
                                                </p>
                                                <p class="text-xs text-gray-500">Prazo estimado: {{ $option['days'] }} {{ $option['days'] === 1 ? 'dia útil' : 'dias úteis' }}</p>
                                            </div>
                                            <span class="text-sm font-bold {{ $option['price'] == 0 ? 'text-emerald-600' : 'text-gray-900' }}">
                                                {{ $option['price'] == 0 ? 'Grátis' : 'R$ ' . number_format($option['price'], 2, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @error('selectedShippingIndex')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                        </div>

                        <div class="mt-4">
                            <button wire:click="goToPayment" wire:loading.attr="disabled"
                                    class="w-full flex items-center justify-center gap-2 py-3 px-6 bg-green-700 text-white font-semibold rounded-xl hover:bg-green-800 transition-colors disabled:opacity-60">
                                <span wire:loading.remove wire:target="goToPayment" class="flex items-center justify-center gap-2">
                                    Continuar para o Pagamento
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                                <span wire:loading wire:target="goToPayment">Validando...</span>
                            </button>
                        </div>
                    @endif
                </div>

            {{-- ══════════════════════════════════════════════════════
                 ETAPA 2 — Pagamento
            ══════════════════════════════════════════════════════ --}}
            @elseif ($step === 2)
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
                    <div class="flex items-center gap-3">
                        <h2 class="text-base font-semibold text-gray-900 flex-1">Forma de pagamento</h2>
                        <button type="button" wire:click="backTo(1)" class="text-sm text-indigo-600 hover:text-indigo-800">← Entrega</button>
                    </div>

                    {{-- Seletor de método --}}
                    <div class="grid grid-cols-2 gap-3">
                        {{-- PIX --}}
                        <div wire:click="$set('paymentMethod','pix')"
                             class="relative p-3.5 border rounded-xl cursor-pointer transition-all
                                {{ $paymentMethod === 'pix' ? 'border-green-500 bg-green-50/60' : 'border-gray-200 hover:border-gray-300' }}">
                            <div class="absolute top-2.5 right-2.5 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors
                                {{ $paymentMethod === 'pix' ? 'border-green-500 bg-green-500' : 'border-gray-300 bg-white' }}">
                                @if ($paymentMethod === 'pix')
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-gray-900 pr-7">PIX</p>
                            <p class="text-xs text-gray-500 mt-0.5">Aprovação imediata</p>
                            @if ($this->pixSavings > 0)
                                <p class="text-xs font-semibold text-emerald-600 mt-1">
                                    Economize R$ {{ number_format($this->pixSavings, 2, ',', '.') }}
                                </p>
                            @endif
                        </div>
                        {{-- Cartão --}}
                        <div wire:click="$set('paymentMethod','credit_card')"
                             class="relative p-3.5 border rounded-xl cursor-pointer transition-all
                                {{ $paymentMethod === 'credit_card' ? 'border-green-500 bg-green-50/60' : 'border-gray-200 hover:border-gray-300' }}">
                            <div class="absolute top-2.5 right-2.5 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors
                                {{ $paymentMethod === 'credit_card' ? 'border-green-500 bg-green-500' : 'border-gray-300 bg-white' }}">
                                @if ($paymentMethod === 'credit_card')
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-gray-900 pr-7">Cartão de crédito</p>
                            <p class="text-xs text-gray-500 mt-0.5">Em até {{ app(\App\Services\PaymentCalculator::class)->installmentsMax() }}×</p>
                        </div>
                    </div>

                    {{-- Formulário cartão --}}
                    @if ($paymentMethod === 'credit_card')
                        <div class="space-y-4 pt-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nome no cartão *</label>
                                <input wire:model="cardHolder" type="text" placeholder="Igual ao cartão" autocomplete="cc-name"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('cardHolder') border-red-400 @enderror">
                                @error('cardHolder') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Número do cartão *</label>
                                <input wire:model.blur="cardNumber" type="text" placeholder="0000 0000 0000 0000" maxlength="19" autocomplete="cc-number"
                                       x-mask="9999 9999 9999 9999"
                                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('cardNumber') border-red-400 @enderror">
                                @error('cardNumber') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Validade *</label>
                                    <input wire:model.blur="cardExpiry" type="text" placeholder="MM/AA" maxlength="5" autocomplete="cc-exp"
                                           x-mask="99/99"
                                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('cardExpiry') border-red-400 @enderror">
                                    @error('cardExpiry') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">CVV *</label>
                                    <input wire:model.blur="cardCvv" type="text" placeholder="000" maxlength="4" autocomplete="cc-csc"
                                           x-mask="9999"
                                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('cardCvv') border-red-400 @enderror">
                                    @error('cardCvv') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Parcelamento</label>
                                <select wire:model="installments"
                                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none bg-white">
                                    @foreach ($this->installmentOptions as $opt)
                                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        {{-- Resumo PIX --}}
                        <div class="p-4 bg-green-50 border border-green-200 rounded-xl space-y-2">
                            @if ($this->pixTotal)
                                <div class="flex items-baseline justify-between">
                                    <span class="text-sm text-green-800">Total no PIX:</span>
                                    <span class="text-xl font-bold text-green-700">R$ {{ number_format($this->pixTotal, 2, ',', '.') }}</span>
                                </div>
                                @if ($this->pixSavings > 0)
                                    <p class="text-sm font-semibold text-emerald-600">
                                        Economia de R$ {{ number_format($this->pixSavings, 2, ',', '.') }} pagando à vista
                                    </p>
                                @endif
                            @endif
                            <p class="text-xs text-green-700/70 pt-1 border-t border-green-200">
                                O QR Code PIX será gerado após a confirmação do pedido.
                            </p>
                        </div>
                    @endif

                    <button wire:click="goToReview"
                            class="w-full flex items-center justify-center gap-2 py-3 px-6 bg-green-700 text-white font-semibold rounded-xl hover:bg-green-800 transition-colors">
                        Revisar pedido
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>

            {{-- ══════════════════════════════════════════════════════
                 ETAPA 3 — Revisão
            ══════════════════════════════════════════════════════ --}}
            @elseif ($step === 3)
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
                                <button type="button" wire:click="backTo(1)" class="text-xs text-indigo-600 hover:text-indigo-800">Alterar</button>
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
                                <button type="button" wire:click="backTo(2)" class="text-xs text-indigo-600 hover:text-indigo-800">Alterar</button>
                            </div>
                            <p class="text-sm text-gray-700">
                                {{ $paymentMethod === 'pix' ? 'PIX' : 'Cartão de crédito' }}
                            </p>
                            @if ($paymentMethod === 'credit_card' && $cardNumber)
                                @php
                                    $chosenOpt      = collect($this->installmentOptions)->firstWhere('value', $installments);
                                    $isInterestFree = $chosenOpt['interest_free'] ?? true;
                                @endphp
                                <p class="text-xs text-gray-500 mt-0.5">
                                    **** {{ substr(preg_replace('/\D/', '', $cardNumber), -4) }}
                                    @if ($installments > 1)
                                        · {{ $installments }}x de R$ {{ number_format($chosenOpt['installment_value'] ?? 0, 2, ',', '.') }} {{ $isInterestFree ? 'sem juros' : 'com juros' }}
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
                                      class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none resize-none transition"></textarea>
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
                            Confirmar Pedido — R$ {{ number_format($this->finalTotal, 2, ',', '.') }} no {{ $paymentMethod === 'pix' ? 'PIX' : 'Cartão de crédito' }}
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
        <div class="lg:sticky lg:top-24 ">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                @php $itemCount = $this->cart->item_count; @endphp

                <h3 class="text-sm font-semibold text-gray-900 mb-4">
                    Resumo do pedido
                    <span class="text-gray-400 font-normal">({{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'itens' }})</span>
                </h3>

                <div class="space-y-3 mb-4">
                    @foreach ($this->cart->items as $item)
                        <div class="flex gap-3 items-center">
                            <div class="w-10 h-10 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->product->name }}" loading="lazy" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-black truncate">{{ $item->product->name }}</p>
                                @if ($item->variant)
                                    <p class="text-xs text-gray-500">{{ $item->variant->label }}</p>
                                @endif
                                <p class="text-xs text-gray-500">Qtd: {{ $item->quantity }}</p>
                            </div>
                            <p class="text-xs font-semibold text-black flex-shrink-0">
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

                    {{-- Desconto PIX (visível a partir da etapa de pagamento) --}}
                    @if ($step >= 2 && $paymentMethod === 'pix' && $this->pixSavings > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Desconto PIX</span>
                            <span>− R$ {{ number_format($this->pixSavings, 2, ',', '.') }}</span>
                        </div>
                    @endif

                    {{-- Juros de parcelamento (visível quando parcelas com juros selecionadas) --}}
                    @if ($step >= 2 && $paymentMethod === 'credit_card' && $this->cardInterestAmount > 0)
                        <div class="flex justify-between text-orange-600">
                            <span>Juros de parcelamento</span>
                            <span>+ R$ {{ number_format($this->cardInterestAmount, 2, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between font-bold text-gray-900 mt-4 pt-4 mb-0 border-t border-gray-100 text-base">
                        <span>Total</span>
                        <span>R$ {{ number_format($step >= 2 ? $this->finalTotal : $this->total, 2, ',', '.') }}</span>
                    </div>

                    @php
                        $calc     = app(\App\Services\PaymentCalculator::class);
                        $pixHint  = $calc->pixPrice($this->total);
                        $instHint = $calc->bestFreeInstallmentLabel($this->total);
                    @endphp
                    @if ($step < 3 && ($pixHint || $instHint))
                        <div class="text-xs text-right text-gray-500 mt-1">
                            @if ($pixHint)
                                <p class="text-green-700 text-end">
                                    <span class="font-semibold">R$ {{ number_format($pixHint, 2, ',', '.') }}</span> no PIX
                                </p>
                            @endif
                            @if ($instHint)
                                <p class="text-gray-500 text-end">ou {{ $instHint }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /grid --}}
</div>
