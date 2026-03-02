<div class="w-full">
    @if ($subscribed)
        <div class="flex flex-col items-center justify-center gap-3 py-4 text-center">
            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-white font-semibold text-lg">Obrigado por se inscrever!</p>
            <p class="text-indigo-200 text-sm">Você receberá nossas novidades em primeira mão.</p>
        </div>
    @else
        <form wire:submit.prevent="subscribe" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input
                    type="text"
                    wire:model="name"
                    placeholder="Seu nome"
                    class="w-full px-4 py-3 rounded-xl text-sm text-gray-900 bg-white border-0
                           focus:ring-0 focus:outline-none placeholder-gray-400
                           @error('name') ring-2 ring-red-400 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex-1">
                <input
                    type="email"
                    wire:model="email"
                    placeholder="Seu e-mail"
                    class="w-full px-4 py-3 rounded-xl text-sm text-gray-900 bg-white border-0
                           focus:ring-0 focus:outline-none placeholder-gray-400
                           @error('email') ring-2 ring-red-400 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex-1">
                <input
                    type="tel"
                    wire:model="phone"
                    x-mask="(99) 99999-9999"
                    placeholder="Seu telefone (opcional)"
                    class="w-full px-4 py-3 rounded-xl text-sm text-gray-900 bg-white border-0
                           focus:ring-0 focus:outline-none placeholder-gray-400
                           @error('phone') ring-2 ring-red-400 @enderror"
                >
                @error('phone')
                    <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                @enderror
            </div>
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="subscribe"
                class="flex-shrink-0 px-6 py-3 bg-green-700 text-sm text-white font-semibold
                       rounded-xl hover:bg-green-800 transition-all duration-150
                       disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="subscribe">Inscrever-se</span>
                <span wire:loading wire:target="subscribe" class="flex items-center gap-1.5">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Enviando...
                </span>
            </button>
        </form>
    @endif
</div>
