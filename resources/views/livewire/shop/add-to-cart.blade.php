<div class="flex items-center gap-3">
    {{-- Seletor de quantidade --}}
    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
        <button type="button"
                wire:click="$set('quantity', max(1, quantity - 1))"
                class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors">
            −
        </button>
        <input type="number"
               wire:model="quantity"
               min="1" max="999"
               class="w-12 text-center text-sm font-medium border-none focus:ring-0 py-2">
        <button type="button"
                wire:click="$set('quantity', quantity + 1)"
                class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors">
            +
        </button>
    </div>

    {{-- Botão adicionar --}}
    <button type="button"
            wire:click="add"
            wire:loading.attr="disabled"
            class="flex-1 flex items-center justify-center gap-2 px-6 py-2 rounded-lg font-semibold text-sm transition-colors
                   {{ $added ? 'bg-green-600 text-white' : 'bg-indigo-600 hover:bg-indigo-700 text-white' }}">
        <span wire:loading.remove wire:target="add">
            {{ $added ? '✓ Adicionado!' : 'Adicionar ao carrinho' }}
        </span>
        <span wire:loading wire:target="add">Adicionando...</span>
    </button>
</div>
