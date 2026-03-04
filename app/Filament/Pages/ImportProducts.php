<?php

namespace App\Filament\Pages;

use App\Imports\ProductCsvImporter;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportProducts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Importar Produtos';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int    $navigationSort  = 10;
    protected static bool    $shouldRegisterNavigation = false;
    protected static string  $view            = 'filament.pages.import-products';
    protected static ?string $title           = 'Importação de Produtos via CSV';

    // Estado do formulário de imagens (independente)
    public ?array $imagesData = [];

    // Estado do formulário de importação CSV
    public ?array $importData = [];

    // Resultados da última importação
    public ?array $results = null;
    public bool   $done    = false;

    // Arquivos atualmente na pasta de staging
    public array $stagingFiles = [];

    public function mount(): void
    {
        $this->imagesForm->fill();
        $this->importForm->fill();
        $this->refreshStagingFiles();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Múltiplos formulários
    // ─────────────────────────────────────────────────────────────────────────

    protected function getForms(): array
    {
        return ['imagesForm', 'importForm'];
    }

    /**
     * Formulário 1 — Upload de imagens para staging.
     * Tem seu próprio botão "Salvar no Staging". Independente do CSV.
     */
    public function imagesForm(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('images')
                    ->label('Selecione as imagens')
                    ->multiple()
                    ->image()
                    ->maxSize(10240)
                    ->maxFiles(500)
                    ->reorderable(false)
                    ->disk('local')
                    ->directory('import/images')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => $file->getClientOriginalName()
                    )
                    ->helperText('JPG, PNG, WebP, GIF — máx 10 MB por arquivo. O nome original é preservado.'),
            ])
            ->statePath('imagesData');
    }

    /**
     * Formulário 2 — Upload do CSV + opções de importação.
     */
    public function importForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Arquivo CSV')
                    ->schema([
                        FileUpload::make('csv_file')
                            ->label('Arquivo CSV')
                            ->required()
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/csv',
                                'application/vnd.ms-excel',
                                'application/octet-stream',
                            ])
                            ->maxSize(20480)
                            ->disk('local')
                            ->directory('import/csv')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => $file->getClientOriginalName()
                            )
                            ->helperText('Delimitador: vírgula (,) ou ponto-e-vírgula (;). Codificação: UTF-8 ou Windows-1252.'),

                        Toggle::make('update_existing')
                            ->label('Atualizar produtos existentes (pelo SKU)')
                            ->helperText('Se ativado e um produto com o mesmo SKU já existir, ele será atualizado. Imagens não são substituídas em atualizações.')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('dry_run')
                            ->label('Modo simulação — não salva nada')
                            ->helperText('Processa o CSV e mostra o resultado sem gravar nada no banco. Útil para validar antes de importar de verdade.')
                            ->default(false)
                            ->inline(false),
                    ]),
            ])
            ->statePath('importData');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Ações do cabeçalho
    // ─────────────────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Baixar Template CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action('downloadTemplate'),
        ];
    }

    public function downloadTemplate(): void
    {
        $csv     = ProductCsvImporter::generateTemplate();
        $encoded = base64_encode($csv);

        $this->dispatch('download-csv-template', content: $encoded, filename: 'template-importacao-produtos.csv');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Salvar imagens no staging (formulário 1)
    // ─────────────────────────────────────────────────────────────────────────

    public function saveImages(): void
    {
        // getState() move os arquivos de livewire-tmp → import/images/
        $this->imagesForm->getState();

        // Reseta o campo de upload para liberar a UI
        $this->imagesForm->fill();

        $this->refreshStagingFiles();

        Notification::make()
            ->title('Imagens salvas no staging!')
            ->body(count($this->stagingFiles) . ' arquivo(s) disponível(is) para vinculação.')
            ->success()
            ->send();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Importar CSV (formulário 2)
    // ─────────────────────────────────────────────────────────────────────────

    public function import(): void
    {
        $this->results = null;
        $this->done    = false;

        $data            = $this->importForm->getState();
        $csvRelativePath = $data['csv_file'] ?? null;

        if (empty($csvRelativePath)) {
            Notification::make()
                ->title('Nenhum arquivo CSV enviado.')
                ->danger()
                ->send();
            return;
        }

        $csvAbsolutePath = Storage::disk('local')->path($csvRelativePath);

        if (! file_exists($csvAbsolutePath)) {
            Notification::make()
                ->title('Arquivo CSV não encontrado no servidor.')
                ->danger()
                ->send();
            return;
        }

        $importer = new ProductCsvImporter(
            updateExisting: (bool) ($data['update_existing'] ?? true),
            dryRun:         (bool) ($data['dry_run'] ?? false),
        );

        $this->results = $importer->import($csvAbsolutePath);
        $this->done    = true;

        // Apaga o CSV processado (mantém as imagens em staging)
        if (! ($data['dry_run'] ?? false)) {
            Storage::disk('local')->delete($csvRelativePath);
        }

        $this->importForm->fill();
        $this->refreshStagingFiles();

        if (empty($this->results['errors'])) {
            Notification::make()
                ->title('Importação concluída!')
                ->body(
                    "Criados: {$this->results['created']} | " .
                    "Atualizados: {$this->results['updated']} | " .
                    "Ignorados: {$this->results['skipped']}"
                )
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Importação concluída com erros.')
                ->body('Verifique os erros e avisos listados abaixo.')
                ->warning()
                ->send();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers de staging
    // ─────────────────────────────────────────────────────────────────────────

    public function clearStagingImages(): void
    {
        foreach (Storage::disk('local')->files('import/images') as $file) {
            Storage::disk('local')->delete($file);
        }
        $this->stagingFiles = [];

        Notification::make()
            ->title('Pasta de staging limpa.')
            ->success()
            ->send();
    }

    public function refreshStagingFiles(): void
    {
        $this->stagingFiles = collect(Storage::disk('local')->files('import/images'))
            ->map(fn ($f) => basename($f))
            ->sort()
            ->values()
            ->toArray();
    }
}
