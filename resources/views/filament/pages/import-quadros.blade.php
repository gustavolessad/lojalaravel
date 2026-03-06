<x-filament-panels::page>

    {{-- ── Upload de imagens (formulário independente) ── --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                <x-heroicon-o-photo class="h-4 w-4 text-gray-500" />
                Upload de Imagens
            </h3>
            <p class="mt-0.5 text-xs text-gray-500">
                Envie todas as imagens de uma vez. O nome original é preservado (ex: <code>AB_00001_CZ.jpg</code>).
                O importador usará os nomes SKU + sufixo + extensão para vincular automaticamente.
            </p>
        </div>
        <div class="p-4">
            <form wire:submit="saveImages">
                {{ $this->imagesForm }}
                <div class="mt-4 flex items-center gap-4">
                    <x-filament::button type="submit" icon="heroicon-o-archive-box-arrow-down" color="gray">
                        Salvar no Staging
                    </x-filament::button>
                    <span wire:loading wire:target="saveImages" class="text-xs text-gray-500">Salvando...</span>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Arquivos em staging ── --}}
    @if (count($stagingFiles) > 0)
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Imagens no staging
                        <span class="ml-2 rounded-full bg-primary-100 px-2 py-0.5 text-xs text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                            {{ count($stagingFiles) }}
                        </span>
                    </h3>
                </div>
                <button
                    type="button"
                    wire:click="clearStagingImages"
                    wire:confirm="Apagar todas as imagens da pasta de staging?"
                    class="text-xs text-red-600 hover:underline dark:text-red-400"
                >
                    Limpar staging
                </button>
            </div>
            <div class="max-h-48 overflow-y-auto px-4 py-2">
                <div class="grid grid-cols-3 gap-x-6 gap-y-0.5 text-xs text-gray-700 dark:text-gray-300 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6">
                    @foreach ($stagingFiles as $file)
                        <span class="truncate py-0.5 font-mono" title="{{ $file }}">{{ $file }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ── Formulário principal ── --}}
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-4">
            <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray" size="lg">
                Importar Quadros
            </x-filament::button>
            <span wire:loading wire:target="submit" class="text-sm text-gray-500 dark:text-gray-400">
                Processando... aguarde.
            </span>
        </div>
    </form>

    {{-- ── Resultado da importação ── --}}
    @if ($done && $results !== null)
        <div class="space-y-4">

            {{-- Resumo --}}
            <div @class([
                'rounded-xl border p-4',
                'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950' => empty($results['errors']),
                'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950' => ! empty($results['errors']),
            ])>
                <h3 @class([
                    'text-base font-semibold mb-3',
                    'text-green-900 dark:text-green-200' => empty($results['errors']),
                    'text-red-900 dark:text-red-200' => ! empty($results['errors']),
                ])>
                    Resultado da importação
                </h3>

                <div class="grid grid-cols-2 gap-4 text-center text-sm">
                    <div class="rounded-lg bg-white/60 px-3 py-2 dark:bg-white/10">
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $results['created'] }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Produtos criados</div>
                    </div>
                    <div class="rounded-lg bg-white/60 px-3 py-2 dark:bg-white/10">
                        <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $results['skus'] }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">SKUs informados</div>
                    </div>
                </div>
            </div>

            {{-- Erros --}}
            @if (! empty($results['errors']))
                <div class="rounded-xl border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950">
                    <div class="flex items-center gap-2 border-b border-red-200 px-4 py-3 dark:border-red-800">
                        <x-heroicon-o-x-circle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-200">
                            Erros ({{ count($results['errors']) }})
                        </h4>
                    </div>
                    <ul class="divide-y divide-red-100 dark:divide-red-900">
                        @foreach ($results['errors'] as $error)
                            <li class="px-4 py-2 text-xs text-red-800 dark:text-red-300">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Avisos --}}
            @if (! empty($results['warnings']))
                <div class="rounded-xl border border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-950">
                    <div class="flex items-center gap-2 border-b border-yellow-200 px-4 py-3 dark:border-yellow-800">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                            Avisos ({{ count($results['warnings']) }})
                        </h4>
                    </div>
                    <ul class="divide-y divide-yellow-100 dark:divide-yellow-900">
                        @foreach ($results['warnings'] as $warning)
                            <li class="px-4 py-2 text-xs text-yellow-800 dark:text-yellow-300">{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    @endif

</x-filament-panels::page>
