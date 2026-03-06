<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-3">
            <x-filament::button type="submit">
                Salvar configurações
            </x-filament::button>

            <x-filament::button
                type="button"
                color="gray"
                wire:click="generatePublicKey"
                wire:loading.attr="disabled"
                wire:target="generatePublicKey"
            >
                <span wire:loading.remove wire:target="generatePublicKey">Gerar chave pública</span>
                <span wire:loading wire:target="generatePublicKey">Gerando...</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
