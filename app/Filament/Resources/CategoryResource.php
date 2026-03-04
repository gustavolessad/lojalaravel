<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Categoria';

    protected static ?string $pluralModelLabel = 'Categorias';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('parent_id')
                    ->label('Categoria pai')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Nenhuma (categoria raiz)')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\SpatieMediaLibraryFileUpload::make('icon')
                    ->label('Imagem / Ícone')
                    ->collection('icon')
                    ->image()
                    ->imageEditor()
                    ->helperText('Usada como ícone da categoria na home. Recomendado: 400×400px.')
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, ?Category $record): string {
                        $slug = Str::slug($record?->name ?? 'categoria');
                        return $slug . '-' . date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 5) . '.jpg';
                    })
                    ->columnSpanFull(),
            ])->columns(2),

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
                        $title = $get('seo_title') ?: $get('name') ?: 'Título da categoria';
                        $desc = $get('seo_description') ?: 'Adicione uma descrição SEO para visualizar...';
                        $slug = $get('slug') ?: 'categoria';
                        $url = url("/{$slug}/c");

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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('icon')
                    ->label('')
                    ->collection('icon')
                    ->conversion('optimized')
                    ->width(40)
                    ->height(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categoria pai')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produtos')
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Categoria pai')
                    ->relationship('parent', 'name'),
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
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
