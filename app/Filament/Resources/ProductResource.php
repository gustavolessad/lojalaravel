<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\AttributeValue;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductTag;
use App\Models\Catalog\ProductVariantGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Group::make()->schema([

                // Informações principais
                Forms\Components\Section::make('Informações Gerais')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome do produto')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\RichEditor::make('description')
                        ->label('Descrição completa')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('brand_id')
                        ->label('Marca')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Sem marca')
                        ->columnSpanFull(),
                ])->columns(2),

                // Preço
                Forms\Components\Section::make('Preço')->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('Preço')
                        ->required()
                        ->numeric()
                        ->prefix('R$')
                        ->live(onBlur: true),

                    Forms\Components\TextInput::make('sale_price')
                        ->label('Preço promocional')
                        ->numeric()
                        ->prefix('R$')
                        ->disabled(fn (Forms\Get $get): bool => blank($get('price')))
                        ->dehydrated()
                        ->helperText('Deve ser menor que o preço normal.')
                        ->rules([
                            fn (Forms\Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get) {
                                if (! filled($value)) return;
                                if (! filled($get('price'))) {
                                    $fail('Preencha o preço normal antes de definir o preço promocional.');
                                    return;
                                }
                                if ((float) $value >= (float) $get('price')) {
                                    $fail('O preço promocional deve ser menor que o preço normal.');
                                }
                            },
                        ]),

                    Forms\Components\DateTimePicker::make('sale_start')
                        ->label('Promoção início')
                        ->native(false)
                        ->displayFormat('d/m/Y H:i'),

                    Forms\Components\DateTimePicker::make('sale_end')
                        ->label('Promoção fim')
                        ->native(false)
                        ->displayFormat('d/m/Y H:i'),
                ])->columns(2),

                // Estoque e identificação
                Forms\Components\Section::make('Estoque e Identificação')->schema([
                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),

                    Forms\Components\TextInput::make('barcode')
                        ->label('Código de barras')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('stock')
                        ->label('Estoque')
                        ->numeric()
                        ->default(0)
                        ->visible(fn (Forms\Get $get) => $get('type') === 'simple'),
                ])->columns(3),

                // Dimensões e peso
                Forms\Components\Section::make('Dimensões e Peso')->schema([
                    Forms\Components\TextInput::make('weight')
                        ->label('Peso (kg)')
                        ->numeric()
                        ->suffix('kg'),

                    Forms\Components\TextInput::make('length')
                        ->label('Comprimento (cm)')
                        ->numeric()
                        ->suffix('cm'),

                    Forms\Components\TextInput::make('width')
                        ->label('Largura (cm)')
                        ->numeric()
                        ->suffix('cm'),

                    Forms\Components\TextInput::make('height')
                        ->label('Altura (cm)')
                        ->numeric()
                        ->suffix('cm'),
                ])->columns(4)->collapsed(),

                // Grupos de imagens (apenas para produto variável)
                Forms\Components\Section::make('Grupos de Imagens')
                    ->description('Crie um grupo por visual (ex: "Preto", "Madeira Clara") e suba as fotos uma única vez. As variantes referenciam o grupo pelo select abaixo.')
                    ->schema([
                        Forms\Components\Repeater::make('variantGroups')
                            ->label('')
                            ->relationship()
                            ->minItems(0)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome do grupo')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Ex: Preto, Madeira Clara...')
                                    ->columnSpan(2),

                                Forms\Components\SpatieMediaLibraryFileUpload::make('group-cover')
                                    ->label('Foto principal')
                                    ->collection('group-cover')
                                    ->image()
                                    ->imageEditor()
                                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record): string {
                                        $slug = Str::slug($record?->product?->name ?? 'produto');
                                        return $slug . '-' . date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 5) . '.jpg';
                                    })
                                    ->columnSpan(1),

                                Forms\Components\SpatieMediaLibraryFileUpload::make('group-gallery')
                                    ->label('Galeria')
                                    ->collection('group-gallery')
                                    ->multiple()
                                    ->image()
                                    ->reorderable()
                                    ->panelLayout('grid')
                                    ->imagePreviewHeight('80')
                                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record): string {
                                        $slug = Str::slug($record?->product?->name ?? 'produto');
                                        return $slug . '-' . date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 5) . '.jpg';
                                    })
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Adicionar grupo de imagens')
                            ->cloneable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => filled($state['name'] ?? '') ? $state['name'] : 'Novo grupo'),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === 'variable'),

                // Variantes (apenas para produto variável)
                Forms\Components\Section::make('Variantes')
                    ->schema([
                        Forms\Components\Select::make('attributes')
                            ->label('Atributos configuráveis')
                            ->relationship('attributes', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('expand_catalog_attributes')
                            ->label('Expandir no catálogo por')
                            ->helperText('O atributo selecionado gerará um card separado por valor no catálogo (ex: um card por cor). Deixe em branco para não expandir.')
                            ->placeholder('Não expandir')
                            ->options(fn (Forms\Get $get): array =>
                                collect($get('attributes') ?? [])
                                    ->mapWithKeys(fn ($id) => [$id => Attribute::find((int) $id)?->name ?? ''])
                                    ->filter()
                                    ->toArray()
                            )
                            ->nullable()
                            ->live()
                            ->visible(fn (Forms\Get $get) => count($get('attributes') ?? []) > 0)
                            ->dehydrated(true)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record?->exists) {
                                    $expandId = $record->attributes()
                                        ->wherePivot('expand_in_catalog', true)
                                        ->value('attributes.id');
                                    $component->state($expandId);
                                }
                            }),

                        Forms\Components\Select::make('variant_image_attribute')
                            ->label('Exibir imagens nos botões de')
                            ->helperText('O atributo selecionado exibirá imagens nos botões de variação na página do produto.')
                            ->placeholder('Nenhum (somente texto)')
                            ->options(fn (Forms\Get $get): array =>
                                collect($get('attributes') ?? [])
                                    ->mapWithKeys(fn ($id) => [$id => Attribute::find((int) $id)?->name ?? ''])
                                    ->filter()
                                    ->toArray()
                            )
                            ->nullable()
                            ->live()
                            ->visible(fn (Forms\Get $get) => count($get('attributes') ?? []) > 0)
                            ->dehydrated(true)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record?->exists) {
                                    $attr = $record->attributes()
                                        ->wherePivotNotIn('variant_display', ['text'])
                                        ->first();
                                    $component->state($attr?->id);
                                }
                            }),

                        Forms\Components\Select::make('variant_image_type')
                            ->label('Tipo de imagem')
                            ->options([
                                'attribute_icon' => 'Ícone do valor do atributo',
                                'group_cover'    => 'Imagem do grupo de imagens',
                            ])
                            ->default('attribute_icon')
                            ->visible(fn (Forms\Get $get) => filled($get('variant_image_attribute')))
                            ->dehydrated(true)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record?->exists) {
                                    $attr = $record->attributes()
                                        ->wherePivotNotIn('variant_display', ['text'])
                                        ->first();
                                    $component->state($attr?->pivot?->variant_display ?? 'attribute_icon');
                                }
                            }),

                        Forms\Components\Repeater::make('variants')
                            ->label('Variantes')
                            ->relationship()
                            ->schema(function (Forms\Get $get) {
                                $attributeIds = $get('attributes') ?? [];
                                $attributes = empty($attributeIds)
                                    ? collect()
                                    : Attribute::with('values')->whereIn('id', $attributeIds)->get();

                                // ── Um Select por atributo (single-select) ─────────
                                // dehydrated(true): valor enviado no submit e convertido
                                // para attribute_value_ids via mutateRelationshipData*
                                $attrSelects = $attributes->isEmpty()
                                    ? [Forms\Components\Placeholder::make('attr_hint')
                                        ->label('Combinação')
                                        ->content('Selecione os atributos configuráveis acima para montar a combinação.')
                                        ->columnSpanFull()]
                                    : $attributes->map(function ($attribute) {
                                        $attrId = $attribute->id;
                                        return Forms\Components\Select::make("attr_val_{$attrId}")
                                            ->label($attribute->name)
                                            ->options($attribute->values->pluck('value', 'id')->toArray())
                                            ->searchable(false)
                                            ->dehydrated(true)
                                            ->live()
                                            ->afterStateHydrated(function ($component, $record) use ($attribute) {
                                                if ($record?->exists) {
                                                    $val = $record->attributeValues
                                                        ->firstWhere('attribute_id', $attribute->id);
                                                    $component->state($val?->id);
                                                }
                                            });
                                    })->toArray();

                                $attrColumns = max(1, $attributes->count());

                                return [
                                    Forms\Components\Grid::make($attrColumns)
                                        ->schema($attrSelects)
                                        ->columnSpanFull(),

                                    // Identificação
                                    Forms\Components\TextInput::make('sku')
                                        ->label('SKU')
                                        ->unique('product_variants', 'sku', ignoreRecord: true)
                                        ->maxLength(100)
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('stock')
                                        ->label('Estoque')
                                        ->numeric()
                                        ->default(0)
                                        ->columnSpan(1),

                                    Forms\Components\Toggle::make('active')
                                        ->label('Ativa')
                                        ->default(true)
                                        ->inline(false)
                                        ->columnSpan(1),

                                    // Preços
                                    Forms\Components\TextInput::make('price')
                                        ->label('Preço')
                                        ->numeric()
                                        ->prefix('R$')
                                        ->placeholder(fn (Forms\Get $get) =>
                                            ($v = $get('../../price'))
                                                ? 'Padrão: R$ ' . number_format((float) $v, 2, ',', '.')
                                                : 'Usa preço do produto')
                                        ->live(onBlur: true)
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('sale_price')
                                        ->label('Preço promocional')
                                        ->numeric()
                                        ->prefix('R$')
                                        ->placeholder(fn (Forms\Get $get) =>
                                            ($v = $get('../../sale_price'))
                                                ? 'Padrão: R$ ' . number_format((float) $v, 2, ',', '.')
                                                : '—')
                                        ->disabled(fn (Forms\Get $get): bool =>
                                            blank($get('price')) && blank($get('../../price'))
                                        )
                                        ->dehydrated()
                                        ->helperText('Deve ser menor que o preço efetivo da variante.')
                                        ->rules([
                                            fn (Forms\Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get) {
                                                if (! filled($value)) return;
                                                $effectivePrice = filled($get('price'))
                                                    ? (float) $get('price')
                                                    : (float) $get('../../price');
                                                if ($effectivePrice <= 0) {
                                                    $fail('Preencha o preço (da variante ou do produto) antes de definir o preço promocional.');
                                                    return;
                                                }
                                                if ((float) $value >= $effectivePrice) {
                                                    $fail('O preço promocional deve ser menor que o preço efetivo da variante.');
                                                }
                                            },
                                        ])
                                        ->columnSpan(2),

                                    // Dimensões
                                    Forms\Components\TextInput::make('weight')
                                        ->label('Peso (kg)')
                                        ->numeric()
                                        ->suffix('kg')
                                        ->placeholder(fn (Forms\Get $get) =>
                                            ($v = $get('../../weight')) ? "{$v} kg" : '—'),

                                    Forms\Components\TextInput::make('length')
                                        ->label('Comprimento (cm)')
                                        ->numeric()
                                        ->suffix('cm')
                                        ->placeholder(fn (Forms\Get $get) =>
                                            ($v = $get('../../length')) ? "{$v} cm" : '—'),

                                    Forms\Components\TextInput::make('width')
                                        ->label('Largura (cm)')
                                        ->numeric()
                                        ->suffix('cm')
                                        ->placeholder(fn (Forms\Get $get) =>
                                            ($v = $get('../../width')) ? "{$v} cm" : '—'),

                                    Forms\Components\TextInput::make('height')
                                        ->label('Altura (cm)')
                                        ->numeric()
                                        ->suffix('cm')
                                        ->placeholder(fn (Forms\Get $get) =>
                                            ($v = $get('../../height')) ? "{$v} cm" : '—'),

                                    // Grupo de imagens
                                    Forms\Components\Select::make('variant_group_id')
                                        ->label('Grupo de imagens')
                                        ->placeholder('Nenhum (usa imagem do produto)')
                                        ->options(function ($livewire) {
                                            return ProductVariantGroup::where('product_id', $livewire->record?->id)
                                                ->orderBy('order')
                                                ->pluck('name', 'id');
                                        })
                                        ->helperText('Crie os grupos na seção acima e salve o produto para associar.')
                                        ->nullable()
                                        ->columnSpan(4),
                                ];
                            })
                            ->columns(4)
                            ->addActionLabel('Adicionar variante')
                            ->reorderable('order')
                            ->cloneable()
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $valueIds = collect($data)
                                    ->filter(fn ($v, $k) => str_starts_with((string) $k, 'attr_val_') && $v)
                                    ->values()
                                    ->toArray();

                                $data['attribute_value_ids'] = $valueIds;

                                foreach (array_keys($data) as $key) {
                                    if (str_starts_with((string) $key, 'attr_val_')) {
                                        unset($data[$key]);
                                    }
                                }

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                $valueIds = collect($data)
                                    ->filter(fn ($v, $k) => str_starts_with((string) $k, 'attr_val_') && $v)
                                    ->values()
                                    ->toArray();

                                $data['attribute_value_ids'] = $valueIds;

                                foreach (array_keys($data) as $key) {
                                    if (str_starts_with((string) $key, 'attr_val_')) {
                                        unset($data[$key]);
                                    }
                                }

                                return $data;
                            })
                            ->collapsible()
                            ->itemLabel(function (array $state): string {
                                $valueIds = collect($state)
                                    ->filter(fn ($v, $k) => str_starts_with($k, 'attr_val_') && $v)
                                    ->values()
                                    ->toArray();

                                if (empty($valueIds)) {
                                    return 'Nova variante';
                                }

                                return AttributeValue::whereIn('id', $valueIds)
                                    ->get()
                                    ->map(fn ($v) => $v->getLabel())
                                    ->implode(' / ');
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === 'variable'),

                // Características do Produto
                Forms\Components\Section::make('Características do Produto')
                    ->description('Informações descritivas exibidas na página do produto e disponíveis como filtros no catálogo. Não criam variantes.')
                    ->schema(function () {
                        $attributes = Attribute::with('values')->orderBy('order')->get();

                        if ($attributes->isEmpty()) {
                            return [
                                Forms\Components\Placeholder::make('char_hint')
                                    ->label('')
                                    ->content('Nenhum atributo cadastrado. Crie em Catálogo → Atributos.'),
                            ];
                        }

                        return $attributes->map(function ($attribute) {
                            return Forms\Components\CheckboxList::make("char_{$attribute->id}")
                                ->label($attribute->name)
                                ->options($attribute->values->pluck('value', 'id'))
                                ->columns(3)
                                ->dehydrated(true)
                                ->visible(fn (Forms\Get $get) => ! in_array(
                                    $attribute->id,
                                    array_map('intval', $get('attributes') ?? [])
                                ))
                                ->afterStateHydrated(function ($component, $record) use ($attribute) {
                                    if ($record?->exists) {
                                        $ids = $record->characteristicValues()
                                            ->where('attribute_values.attribute_id', $attribute->id)
                                            ->pluck('attribute_values.id')
                                            ->toArray();
                                        $component->state($ids);
                                    }
                                });
                        })->toArray();
                    })
                    ->collapsed()
                    ->collapsible(),

                // SEO
                Forms\Components\Section::make('SEO')->schema([
                    Forms\Components\TextInput::make('seo_title')
                        ->label('Título SEO')
                        ->maxLength(60)
                        ->live(debounce: '500ms')
                        ->hint(fn (?string $state): string => mb_strlen($state ?? '') . '/60')
                        ->hintColor(fn (?string $state) => mb_strlen($state ?? '') > 60 ? 'danger' : (mb_strlen($state ?? '') > 50 ? 'warning' : null)),
                    Forms\Components\Textarea::make('seo_description')
                        ->label('Descrição SEO')
                        ->rows(2)
                        ->maxLength(160)
                        ->live(debounce: '500ms')
                        ->hint(fn (?string $state): string => mb_strlen($state ?? '') . '/160')
                        ->hintColor(fn (?string $state) => mb_strlen($state ?? '') > 160 ? 'danger' : (mb_strlen($state ?? '') > 150 ? 'warning' : null)),
                    Forms\Components\TextInput::make('seo_keywords')
                        ->label('Palavras-chave SEO')
                        ->maxLength(255)
                        ->placeholder('palavra1, palavra2, palavra3')
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('serp_preview')
                        ->label('')
                        ->content(function (Forms\Get $get): HtmlString {
                            $title = $get('seo_title') ?: $get('name') ?: 'Título do produto';
                            $desc = $get('seo_description') ?: 'Adicione uma descrição SEO para visualizar...';
                            $slug = $get('slug') ?: 'produto';
                            $url = url("/{$slug}/p");

                            return new HtmlString(
                                '<div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">'
                                . '<p class="text-xs font-medium text-gray-400 mb-2">Preview do Google</p>'
                                . '<cite class="text-sm text-[#202124] dark:text-gray-300 not-italic">' . e($url) . '</cite>'
                                . '<h3 class="text-xl text-[#1a0dab] dark:text-blue-400 leading-snug truncate mt-0.5">' . e(mb_substr($title, 0, 60)) . '</h3>'
                                . '<p class="text-sm text-[#4d5156] dark:text-gray-400 mt-0.5 line-clamp-2">' . e(mb_substr($desc, 0, 160)) . '</p>'
                                . '</div>'
                            );
                        })
                        ->columnSpanFull(),
                ])->columns(2)->collapsed(),

            ])->columnSpan(['lg' => 2]),

            // Sidebar
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Status')->schema([
                    Forms\Components\Select::make('type')
                        ->label('Tipo de produto')
                        ->options([
                            'simple'   => 'Simples',
                            'variable' => 'Variável',
                        ])
                        ->required()
                        ->default('simple')
                        ->live(),

                    Forms\Components\Toggle::make('active')
                        ->label('Ativo')
                        ->default(true),

                    Forms\Components\Toggle::make('featured')
                        ->label('Destaque'),

                    Forms\Components\Toggle::make('is_new')
                        ->label('Novidade'),
                ]),

                Forms\Components\Section::make('Categorias')->schema([
                    Forms\Components\CheckboxList::make('categories')
                        ->label('')
                        ->relationship('categories', 'name')
                        ->allowHtml()
                        ->options(function (): array {
                            $options = [];
                            $buildLevel = function ($categories, int $depth = 0) use (&$buildLevel, &$options) {
                                foreach ($categories as $cat) {
                                    $paddingLeft = $depth * 16; // px por nível
                                    $label = $depth > 0
                                        ? '<span style="display:flex;align-items:center;padding-left:' . $paddingLeft . 'px"><span style="color:#9ca3af;margin-right:4px">↳</span>' . e($cat->name) . '</span>'
                                        : e($cat->name);
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
                        ->searchable()
                        ->bulkToggleable()
                        ->columns(1),
                ]),

                Forms\Components\Section::make('Compre Junto')->schema([
                    Forms\Components\Select::make('compraJunto')
                        ->label('')
                        ->relationship(
                            name: 'compraJunto',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query, $livewire) => $query->when(
                                $livewire->record?->id,
                                fn ($q) => $q->where('id', '!=', $livewire->record->id)
                            )
                        )
                        ->multiple()
                        ->maxItems(3)
                        ->searchable()
                        ->preload()
                        ->placeholder('Buscar produtos... (máx. 3)'),
                ]),

                Forms\Components\Section::make('Produtos Recomendados')->schema([
                    Forms\Components\Select::make('recommended')
                        ->label('')
                        ->relationship(
                            name: 'recommended',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query, $livewire) => $query->when(
                                $livewire->record?->id,
                                fn ($q) => $q->where('id', '!=', $livewire->record->id)
                            )
                        )
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->placeholder('Buscar produtos recomendados...'),
                ]),

                Forms\Components\Section::make('Tags')->schema([
                    Forms\Components\Select::make('tags')
                        ->label('')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->searchable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->label('Nome')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                            Forms\Components\TextInput::make('slug')
                                ->label('Slug')
                                ->required(),
                        ]),
                ]),

                Forms\Components\Section::make('Imagens')->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('cover')
                        ->label('Imagem de capa')
                        ->collection('cover')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageEditor()
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, ?Product $record): string {
                            $slug = Str::slug($record?->name ?? 'produto');
                            return $slug . '-' . date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 5) . '.jpg';
                        }),

                    Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
                        ->label('Galeria')
                        ->collection('gallery')
                        ->multiple()
                        ->image()
                        ->reorderable()
                        ->panelLayout('grid')
                        ->imagePreviewHeight('80')
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, ?Product $record): string {
                            $slug = Str::slug($record?->name ?? 'produto');
                            return $slug . '-' . date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 5) . '.jpg';
                        }),
                ]),

            ])->columnSpan(['lg' => 1]),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')
                    ->label('')
                    ->collection('cover')
                    ->conversion('thumb')
                    ->width(48)
                    ->height(48)
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=P&size=48&background=e5e7eb'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'simple' ? 'Simples' : 'Variável')
                    ->color(fn ($state) => $state === 'simple' ? 'primary' : 'warning'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Estoque')
                    ->formatStateUsing(fn (Product $record): string =>
                        $record->type === 'variable'
                            ? (string) $record->variants()->sum('stock')
                            : ($record->stock !== null ? (string) (int) $record->stock : '—')
                    )
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('active')
                    ->label('Ativo'),

                Tables\Columns\ToggleColumn::make('featured')
                    ->label('Destaque'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(['simple' => 'Simples', 'variable' => 'Variável']),

                Tables\Filters\TernaryFilter::make('active')->label('Ativo'),
                Tables\Filters\TernaryFilter::make('featured')->label('Destaque'),

                Tables\Filters\SelectFilter::make('categories')
                    ->label('Categoria')
                    ->relationship('categories', 'name')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
