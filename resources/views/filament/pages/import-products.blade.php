<x-filament-panels::page>

    {{-- Download CSV template via JS --}}
    <div
        x-data
        x-on:download-csv-template.window="
            const link = document.createElement('a');
            link.href  = 'data:text/csv;charset=utf-8;base64,' + $event.detail.content;
            link.setAttribute('download', $event.detail.filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        "
    ></div>

    {{-- ──────────────────────────────────────────────────────────── --}}
    {{-- Instruções                                                   --}}
    {{-- ──────────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-200">
        <p class="font-semibold mb-1">Como usar:</p>
        <ol class="list-decimal list-inside space-y-1">
            <li>Baixe o <strong>Template CSV</strong> no botão acima e preencha com seus produtos.</li>
            <li><em>(Opcional)</em> Envie as imagens na seção abaixo e clique <strong>Salvar no Staging</strong>. Elas ficam guardadas e são vinculadas pelo nome do arquivo que você colocar no CSV.</li>
            <li>Envie o CSV e clique <strong>Importar Produtos</strong>.</li>
        </ol>
    </div>

    {{-- ──────────────────────────────────────────────────────────── --}}
    {{-- SEÇÃO 1 — Upload de imagens (formulário independente)        --}}
    {{-- ──────────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                <x-heroicon-o-photo class="h-4 w-4 text-gray-500" />
                1. Upload de Imagens (opcional)
            </h3>
            <p class="mt-0.5 text-xs text-gray-500">
                Envie antes de importar o CSV. As imagens ficam guardadas na pasta de staging e são vinculadas pelo nome do arquivo que você informar nas colunas <code>cover_image</code> e <code>gallery_images</code>.
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

    {{-- ──────────────────────────────────────────────────────────── --}}
    {{-- Arquivos atualmente em staging                               --}}
    {{-- ──────────────────────────────────────────────────────────── --}}
    @if (count($stagingFiles) > 0)
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Imagens disponíveis no staging
                        <span class="ml-2 rounded-full bg-primary-100 px-2 py-0.5 text-xs text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                            {{ count($stagingFiles) }}
                        </span>
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Use estes nomes exatamente nas colunas <code>cover_image</code> e <code>gallery_images</code> do CSV.
                    </p>
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
                <div class="grid grid-cols-2 gap-x-6 gap-y-0.5 text-xs text-gray-700 dark:text-gray-300 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    @foreach ($stagingFiles as $file)
                        <span class="truncate py-0.5 font-mono" title="{{ $file }}">{{ $file }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ──────────────────────────────────────────────────────────── --}}
    {{-- SEÇÃO 2 — Importação CSV (formulário independente)           --}}
    {{-- ──────────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                <x-heroicon-o-table-cells class="h-4 w-4 text-gray-500" />
                2. Importar CSV
            </h3>
        </div>
        <div class="p-4">
            <form wire:submit="import">
                {{ $this->importForm }}
                <div class="mt-4 flex items-center gap-4">
                    <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray" size="lg">
                        Importar Produtos
                    </x-filament::button>
                    <span wire:loading wire:target="import" class="text-sm text-gray-500 dark:text-gray-400">
                        Processando... aguarde.
                    </span>
                </div>
            </form>
        </div>
    </div>

    {{-- ──────────────────────────────────────────────────────────── --}}
    {{-- Resultado da importação                                      --}}
    {{-- ──────────────────────────────────────────────────────────── --}}
    @if ($done && $results !== null)
        <div class="space-y-4">

            {{-- Resumo --}}
            <div @class([
                'rounded-xl border p-4',
                'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950' => empty($results['errors']),
                'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-950' => ! empty($results['errors']),
            ])>
                <h3 @class([
                    'text-base font-semibold mb-3',
                    'text-green-900 dark:text-green-200' => empty($results['errors']),
                    'text-yellow-900 dark:text-yellow-200' => ! empty($results['errors']),
                ])>
                    @if ($results['dry_run'])
                        Resultado da simulação (nada foi salvo)
                    @else
                        Resultado da importação
                    @endif
                </h3>

                <div class="grid grid-cols-3 gap-4 text-center text-sm">
                    <div class="rounded-lg bg-white/60 px-3 py-2 dark:bg-white/10">
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $results['created'] }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Criados</div>
                    </div>
                    <div class="rounded-lg bg-white/60 px-3 py-2 dark:bg-white/10">
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $results['updated'] }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Atualizados</div>
                    </div>
                    <div class="rounded-lg bg-white/60 px-3 py-2 dark:bg-white/10">
                        <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $results['skipped'] }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Ignorados</div>
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

            {{-- Avisos (inclui imagens não encontradas) --}}
            @if (! empty($results['warnings']))
                <div class="rounded-xl border border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-950">
                    <div class="flex items-center gap-2 border-b border-yellow-200 px-4 py-3 dark:border-yellow-800">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                            Avisos — {{ count($results['warnings']) }}
                            (imagens não encontradas aparecem aqui)
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
