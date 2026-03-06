<?php

namespace App\Filament\Pages;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantGroup;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportQuadros extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Importador de Quadros';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int    $navigationSort  = 11;
    protected static string  $view            = 'filament.pages.import-quadros';
    protected static ?string $title           = 'Importador de Quadros';

    // IDs fixos dos atributos usados para quadros
    private const ATTR_COR_MOLDURA_SLUG    = 'cor-da-moldura';
    private const ATTR_TAMANHO_QUADRO_SLUG = 'tamanho-do-quadro';

    public ?array $data = [];
    public ?array $imagesData = [];
    public array $stagingFiles = [];

    public ?array $results = null;
    public bool $done = false;

    public function mount(): void
    {
        $this->form->fill();
        $this->imagesForm->fill();
        $this->refreshStagingFiles();
    }

    protected function getForms(): array
    {
        return ['form', 'imagesForm'];
    }

    // ── Upload de imagens (formulário independente) ──────────────────────

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
                    ->helperText('JPG, PNG, WebP — máx 10 MB por arquivo. O nome original é preservado.'),
            ])
            ->statePath('imagesData');
    }

    public function saveImages(): void
    {
        $this->imagesForm->getState();
        $this->imagesForm->fill();
        $this->refreshStagingFiles();

        Notification::make()
            ->title('Imagens salvas no staging!')
            ->body(count($this->stagingFiles) . ' arquivo(s) disponível(is).')
            ->success()
            ->send();
    }

    public function clearStagingImages(): void
    {
        foreach (Storage::disk('local')->files('import/images') as $file) {
            Storage::disk('local')->delete($file);
        }
        $this->stagingFiles = [];

        Notification::make()->title('Pasta de staging limpa.')->success()->send();
    }

    public function refreshStagingFiles(): void
    {
        $this->stagingFiles = collect(Storage::disk('local')->files('import/images'))
            ->map(fn ($f) => basename($f))
            ->sort()
            ->values()
            ->toArray();
    }

    // ── Importação ───────────────────────────────────────────────────────

    public function submit(): void
    {
        $this->results = null;
        $this->done    = false;

        $data = $this->form->getState();

        // Campos dinâmicos (suffix_cover_*, suffix_gallery_*, price_*) podem não
        // vir no getState() quando gerados via spread. Merge com $this->data.
        $data = array_merge($this->data, $data);

        $skus = array_filter(array_map('trim', explode(',', $data['skus'] ?? '')));

        if (empty($skus)) {
            Notification::make()->title('Nenhum SKU informado.')->danger()->send();
            return;
        }

        $productName = $data['product_name'] ?? '';
        $description = $data['description'] ?? null;
        $extension   = ltrim($data['image_extension'] ?? 'jpg', '.');
        $categoryIds = $data['categories'] ?? [];
        $brandId     = $data['brand_id'] ?? null;
        $isActive    = (bool) ($data['is_active'] ?? true);
        $isFeatured  = (bool) ($data['is_featured'] ?? false);
        $isNew       = (bool) ($data['is_new'] ?? true);

        // Atributos
        $attrCorMoldura    = Attribute::where('slug', self::ATTR_COR_MOLDURA_SLUG)->first();
        $attrTamanhoQuadro = Attribute::where('slug', self::ATTR_TAMANHO_QUADRO_SLUG)->first();

        if (! $attrCorMoldura || ! $attrTamanhoQuadro) {
            Notification::make()
                ->title('Atributos "Cor da Moldura" ou "Tamanho do Quadro" não encontrados.')
                ->danger()
                ->send();
            return;
        }

        // Valores selecionados
        $corIds     = $data['cores'] ?? [];
        $tamanhoIds = $data['tamanhos'] ?? [];

        if (empty($corIds) || empty($tamanhoIds)) {
            Notification::make()->title('Selecione pelo menos uma cor e um tamanho.')->danger()->send();
            return;
        }

        $cores    = AttributeValue::whereIn('id', $corIds)->get();
        $tamanhos = AttributeValue::whereIn('id', $tamanhoIds)->get();

        // Sufixos por cor
        $corSuffixes = [];
        foreach ($cores as $cor) {
            $key = (string) $cor->id;
            $corSuffixes[$key] = [
                'cover'   => $data["suffix_cover_{$cor->id}"] ?? '',
                'gallery' => $data["suffix_gallery_{$cor->id}"] ?? '',
            ];
        }

        // Preços por tamanho
        $precoPorTamanho = [];
        foreach ($tamanhos as $tam) {
            $precoPorTamanho[$tam->id] = (float) ($data["price_{$tam->id}"] ?? 0);
        }

        $basePrice = min($precoPorTamanho) ?: 0;

        $created  = 0;
        $errors   = [];
        $warnings = [];

        DB::beginTransaction();

        try {
            foreach ($skus as $sku) {
                try {
                    // ── Verifica se SKU já existe ────────────────────
                    $existingVariant = ProductVariant::where('sku', 'like', $sku . '-%')->first();
                    if ($existingVariant) {
                        $warnings[] = "SKU {$sku}: produto já existe (variante {$existingVariant->sku}). Pulado.";
                        continue;
                    }

                    // ── Criar Product ────────────────────────────────
                    $fullName = $productName . ' — ' . $sku;
                    $slug     = Str::slug($fullName);

                    $originalSlug = $slug;
                    $counter      = 1;
                    while (Product::where('slug', $slug)->exists()) {
                        $slug = $originalSlug . '-' . $counter++;
                    }

                    $product = Product::create([
                        'type'        => 'variable',
                        'name'        => $fullName,
                        'slug'        => $slug,
                        'description' => $description,
                        'price'       => $basePrice,
                        'stock'       => 0,
                        'brand_id'    => $brandId,
                        'active'      => $isActive,
                        'featured'    => $isFeatured,
                        'is_new'      => $isNew,
                    ]);

                    // ── Categorias ───────────────────────────────────
                    if (! empty($categoryIds)) {
                        $product->categories()->sync($categoryIds);
                    }

                    // ── Atributos do produto ─────────────────────────
                    $product->attributes()->sync([
                        $attrCorMoldura->id    => ['expand_in_catalog' => false],
                        $attrTamanhoQuadro->id => ['expand_in_catalog' => false],
                    ]);

                    // ── Grupos de imagens (1 por cor) ────────────────
                    $groupMap = []; // corId => ProductVariantGroup
                    foreach ($cores as $corIndex => $cor) {
                        $group = ProductVariantGroup::create([
                            'product_id' => $product->id,
                            'name'       => $cor->value,
                            'order'      => $corIndex,
                        ]);

                        $suffix = $corSuffixes[$cor->id] ?? [];

                        // Cover do grupo
                        if (! empty($suffix['cover'])) {
                            $coverFile = $sku . $suffix['cover'] . '.' . $extension;
                            $this->attachImage($group, $coverFile, 'group-cover', $warnings);
                        }

                        // Galeria do grupo
                        if (! empty($suffix['gallery'])) {
                            $gallerySuffixes = array_filter(array_map('trim', explode(',', $suffix['gallery'])));
                            foreach ($gallerySuffixes as $gs) {
                                $galleryFile = $sku . $gs . '.' . $extension;
                                $this->attachImage($group, $galleryFile, 'group-gallery', $warnings);
                            }
                        }

                        $groupMap[$cor->id] = $group;
                    }

                    // ── Variantes (cor × tamanho) ────────────────────
                    $variantOrder = 0;
                    foreach ($cores as $cor) {
                        foreach ($tamanhos as $tam) {
                            // Dimensões extraídas do tamanho (ex: "30x40" → 30, 40)
                            [$width, $height] = $this->parseDimensions($tam->value);

                            $variantSku = $sku . '-' . Str::slug($cor->value) . '-' . Str::slug($tam->value);

                            // SKU único
                            $originalVSku = $variantSku;
                            $vCounter     = 1;
                            while (ProductVariant::where('sku', $variantSku)->exists()) {
                                $variantSku = $originalVSku . '-' . $vCounter++;
                            }

                            $variant = ProductVariant::create([
                                'product_id'       => $product->id,
                                'variant_group_id' => $groupMap[$cor->id]->id,
                                'sku'              => $variantSku,
                                'price'            => $precoPorTamanho[$tam->id] ?? 0,
                                'stock'            => 9999,
                                'weight'           => 1.000,
                                'width'            => $width,
                                'height'           => $height,
                                'length'           => 10,
                                'active'           => true,
                                'order'            => $variantOrder++,
                            ]);

                            $variant->attributeValues()->sync([$cor->id, $tam->id]);
                        }
                    }

                    // Atualizar estoque e preço do produto
                    $product->update([
                        'stock' => $product->variants()->sum('stock'),
                    ]);

                    $created++;
                } catch (\Throwable $e) {
                    $errors[] = "SKU {$sku}: " . $e->getMessage();
                    Log::error("ImportQuadros erro SKU {$sku}", [
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ]);
                }
            }

            if (empty($errors)) {
                DB::commit();
            } else {
                DB::rollBack();
                $warnings[] = 'Nenhum produto foi salvo porque houve erros. Corrija e tente novamente.';
                $created = 0;
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = 'Erro fatal: ' . $e->getMessage();
            Log::error('ImportQuadros erro fatal', ['message' => $e->getMessage()]);
        }

        $this->results = [
            'created'  => $created,
            'skus'     => count($skus),
            'errors'   => $errors,
            'warnings' => $warnings,
        ];
        $this->done = true;

        if (empty($errors)) {
            Notification::make()
                ->title('Importação concluída!')
                ->body("{$created} produto(s) criado(s) com sucesso.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Importação falhou')
                ->body('Verifique os erros abaixo.')
                ->danger()
                ->send();
        }
    }

    /**
     * Extrai largura e altura de um valor de tamanho como "30x40".
     * Retorna [largura, altura]. Se não conseguir parsear, retorna [0, 0].
     */
    private function parseDimensions(string $tamanho): array
    {
        if (preg_match('/(\d+)\s*x\s*(\d+)/i', $tamanho, $matches)) {
            return [(float) $matches[1], (float) $matches[2]];
        }

        return [0, 0];
    }

    private function attachImage(
        ProductVariantGroup $model,
        string $filename,
        string $collection,
        array &$warnings,
    ): void {
        $relativePath = 'import/images/' . $filename;

        if (! Storage::disk('local')->exists($relativePath)) {
            $warnings[] = "Imagem não encontrada no staging: {$filename}";
            return;
        }

        $absolutePath = Storage::disk('local')->path($relativePath);

        try {
            $model->addMedia($absolutePath)
                ->preservingOriginal()
                ->toMediaCollection($collection);
        } catch (\Throwable $e) {
            $warnings[] = "Erro ao anexar '{$filename}': " . $e->getMessage();
        }
    }

    // ── Formulário principal ────────────────────────────────────────────

    public function form(Form $form): Form
    {
        $attrCorMoldura    = Attribute::where('slug', self::ATTR_COR_MOLDURA_SLUG)->first();
        $attrTamanhoQuadro = Attribute::where('slug', self::ATTR_TAMANHO_QUADRO_SLUG)->first();

        return $form
            ->schema([
                Split::make([
                    // ═══ Coluna principal ═══
                    \Filament\Forms\Components\Group::make([

                        // ── Dados do produto ────────────────────────
                        Section::make('Dados do produto')
                            ->schema([
                                TextInput::make('product_name')
                                    ->label('Nome do produto')
                                    ->required()
                                    ->placeholder('Quadro Decorativo')
                                    ->helperText('Título final: "Nome — SKU". Ex: "Quadro Decorativo — AB_00001"'),

                                RichEditor::make('description')
                                    ->label('Descrição')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline',
                                        'bulletList', 'orderedList',
                                        'link', 'undo', 'redo',
                                    ]),
                            ])
                            ->compact(),

                        // ── SKUs ─────────────────────────────────────
                        Section::make('SKUs dos produtos')
                            ->description('Cada SKU gerará um produto variável. Ex: AB_00001, AB_00002')
                            ->schema([
                                Textarea::make('skus')
                                    ->label('SKUs')
                                    ->placeholder('AB_00001, AB_00002, AB_00003')
                                    ->rows(2)
                                    ->required()
                                    ->helperText('Separados por vírgula.'),
                            ])
                            ->compact(),

                        // ── Cores e Imagens ──────────────────────────
                        Section::make('Cores da Moldura')
                            ->description('Cada cor selecionada gerará um grupo de imagens. Informe o sufixo dos arquivos para cada cor.')
                            ->schema([
                                Select::make('cores')
                                    ->label('Cores')
                                    ->multiple()
                                    ->options(
                                        $attrCorMoldura
                                            ? $attrCorMoldura->values()->orderBy('order')->pluck('value', 'id')->toArray()
                                            : []
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),

                                TextInput::make('image_extension')
                                    ->label('Extensão dos arquivos')
                                    ->placeholder('jpg')
                                    ->default('jpg')
                                    ->helperText('Arquivo = SKU + sufixo + .extensão'),

                                // Sufixos dinâmicos por cor selecionada
                                Placeholder::make('suffixes_header')
                                    ->label('')
                                    ->content(new HtmlString('<div class="text-sm font-medium text-gray-700 dark:text-gray-300 -mb-2">Sufixos de imagem por cor</div>'))
                                    ->visible(fn (Get $get): bool => ! empty($get('cores'))),

                                ...($attrCorMoldura
                                    ? $attrCorMoldura->values()->orderBy('order')->get()->map(function ($val) {
                                        return Grid::make(3)
                                            ->schema([
                                                Placeholder::make("cor_label_{$val->id}")
                                                    ->label('')
                                                    ->content(new HtmlString("<span class=\"font-semibold text-sm\">{$val->value}</span>")),

                                                TextInput::make("suffix_cover_{$val->id}")
                                                    ->label('Sufixo cover')
                                                    ->placeholder('_PR'),

                                                TextInput::make("suffix_gallery_{$val->id}")
                                                    ->label('Sufixos galeria (vírgula)')
                                                    ->placeholder('_D_PR, _E_PR'),
                                            ])
                                            ->visible(fn (Get $get): bool => in_array($val->id, $get('cores') ?? []));
                                    })->toArray()
                                    : []
                                ),
                            ])
                            ->compact(),

                        // ── Tamanhos e Preços ────────────────────────
                        Section::make('Tamanhos e Preços')
                            ->description('Cada tamanho selecionado terá um preço. Dimensões e peso são calculados automaticamente.')
                            ->schema([
                                Select::make('tamanhos')
                                    ->label('Tamanhos')
                                    ->multiple()
                                    ->options(
                                        $attrTamanhoQuadro
                                            ? $attrTamanhoQuadro->values()->orderBy('order')->pluck('value', 'id')->toArray()
                                            : []
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),

                                // Campos de preço dinâmicos por tamanho selecionado
                                ...($attrTamanhoQuadro
                                    ? $attrTamanhoQuadro->values()->orderBy('order')->get()->map(function ($val) {
                                        return TextInput::make("price_{$val->id}")
                                            ->label("Preço — {$val->value}")
                                            ->numeric()
                                            ->prefix('R$')
                                            ->required()
                                            ->visible(fn (Get $get): bool => in_array($val->id, $get('tamanhos') ?? []));
                                    })->toArray()
                                    : []
                                ),

                                // Resumo das combinações
                                Placeholder::make('combinations_preview')
                                    ->label('Combinações que serão geradas')
                                    ->content(function (Get $get) use ($attrCorMoldura, $attrTamanhoQuadro): HtmlString {
                                        $corIds     = $get('cores') ?? [];
                                        $tamanhoIds = $get('tamanhos') ?? [];

                                        if (empty($corIds) || empty($tamanhoIds)) {
                                            return new HtmlString('<span class="text-gray-400 text-xs">Selecione cores e tamanhos para ver as combinações.</span>');
                                        }

                                        $cores    = $attrCorMoldura ? AttributeValue::whereIn('id', $corIds)->pluck('value') : collect();
                                        $tamanhos = $attrTamanhoQuadro ? AttributeValue::whereIn('id', $tamanhoIds)->pluck('value') : collect();

                                        $total = $cores->count() * $tamanhos->count();
                                        $lines = [];
                                        foreach ($cores as $cor) {
                                            foreach ($tamanhos as $tam) {
                                                $lines[] = "<span class=\"inline-block rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-800\">{$cor} / {$tam}</span>";
                                            }
                                        }

                                        return new HtmlString(
                                            "<div class=\"text-xs text-gray-600 dark:text-gray-400 mb-1\">{$total} variante(s) por produto</div>" .
                                            '<div class="flex flex-wrap gap-1">' . implode('', $lines) . '</div>'
                                        );
                                    })
                                    ->visible(fn (Get $get): bool => ! empty($get('cores')) && ! empty($get('tamanhos'))),
                            ])
                            ->compact(),
                    ]),

                    // ═══ Sidebar ═══
                    \Filament\Forms\Components\Group::make([
                        Section::make('Categorias')
                            ->schema([
                                CheckboxList::make('categories')
                                    ->label('')
                                    ->options(function (): array {
                                        $options = [];
                                        $buildLevel = function ($categories, int $depth = 0) use (&$buildLevel, &$options) {
                                            foreach ($categories as $cat) {
                                                $paddingLeft = $depth * 16;
                                                $label = $depth > 0
                                                    ? '<span style="display:flex;align-items:center;padding-left:' . $paddingLeft . 'px"><span style="color:#9ca3af;margin-right:4px">↳</span>' . e($cat->name) . '</span>'
                                                    : '<span style="font-weight:600">' . e($cat->name) . '</span>';
                                                $options[$cat->id] = $label;
                                                if ($cat->children->isNotEmpty()) {
                                                    $buildLevel($cat->children, $depth + 1);
                                                }
                                            }
                                        };
                                        $buildLevel(
                                            Category::with('children.children')
                                                ->whereNull('parent_id')
                                                ->orderBy('name')
                                                ->get()
                                        );
                                        return $options;
                                    })
                                    ->allowHtml()
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->columns(1),
                            ])
                            ->compact(),

                        Section::make('Marca')
                            ->schema([
                                Select::make('brand_id')
                                    ->label('')
                                    ->options(fn () => Brand::orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Selecione a marca'),
                            ])
                            ->compact(),

                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Ativo')
                                    ->default(true)
                                    ->inline(false),

                                Toggle::make('is_featured')
                                    ->label('Destaque')
                                    ->default(false)
                                    ->inline(false),

                                Toggle::make('is_new')
                                    ->label('Novidade')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->compact(),
                    ])->grow(false),
                ])->from('md'),
            ])
            ->statePath('data');
    }
}
