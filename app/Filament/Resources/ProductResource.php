<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
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

                    Forms\Components\Textarea::make('short_description')
                        ->label('Descrição curta')
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),

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
                        ->prefix('R$'),

                    Forms\Components\TextInput::make('sale_price')
                        ->label('Preço promocional')
                        ->numeric()
                        ->prefix('R$'),

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
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('sale_price')
                                        ->label('Preço promocional')
                                        ->numeric()
                                        ->prefix('R$')
                                        ->placeholder(fn (Forms\Get $get) =>
                                            ($v = $get('../../sale_price'))
                                                ? 'Padrão: R$ ' . number_format((float) $v, 2, ',', '.')
                                                : '—')
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

                                    // Imagens
                                    Forms\Components\SpatieMediaLibraryFileUpload::make('variant-cover')
                                        ->label('Foto da variante')
                                        ->collection('variant-cover')
                                        ->image()
                                        ->imageEditor()
                                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record): string {
                                            $ext         = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                                            $productSlug = Str::slug($record?->product?->name ?? $record?->name ?? 'variante');
                                            $sku         = Str::slug($record?->sku ?? substr(uniqid(), -5));
                                            return "{$productSlug}-{$sku}.{$ext}";
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\SpatieMediaLibraryFileUpload::make('variant-gallery')
                                        ->label('Galeria da variante')
                                        ->collection('variant-gallery')
                                        ->multiple()
                                        ->image()
                                        ->reorderable()
                                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record): string {
                                            $ext         = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                                            $productSlug = Str::slug($record?->product?->name ?? $record?->name ?? 'variante');
                                            return "{$productSlug}-" . substr(uniqid(), -5) . ".{$ext}";
                                        })
                                        ->columnSpan(2),
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

                // SEO
                Forms\Components\Section::make('SEO')->schema([
                    Forms\Components\TextInput::make('seo_title')
                        ->label('Título SEO')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('seo_description')
                        ->label('Descrição SEO')
                        ->maxLength(255),
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
                    Forms\Components\Select::make('categories')
                        ->label('')
                        ->relationship('categories', 'name')
                        ->multiple()
                        ->preload()
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
                            $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                            $slug = Str::slug($record?->name ?? 'produto');
                            return "{$slug}.{$ext}";
                        }),

                    Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
                        ->label('Galeria')
                        ->collection('gallery')
                        ->multiple()
                        ->image()
                        ->reorderable()
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, ?Product $record): string {
                            $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                            $slug = Str::slug($record?->name ?? 'produto');
                            return "{$slug}-" . substr(uniqid(), -5) . ".{$ext}";
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
