<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Banners;
use App\Filament\Resources\BannerPrincipalResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BannerPrincipalResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $cluster = Banners::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Banner Principal';
    protected static ?string $modelLabel      = 'Banner Principal';
    protected static ?string $pluralModelLabel = 'Banners Principais';
    protected static ?int    $navigationSort  = 1;

    // ── Escopo: apenas banners do grupo "principal" ───────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('group', 'principal');
    }

    // ── Formulário ────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título (controle interno)')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Imagens')->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('desktop')
                    ->label('Imagem Desktop')
                    ->helperText('Recomendado: 1920×600px. Caso seja maior, será reduzida automaticamente para 1920px de largura.')
                    ->collection('desktop')
                    ->image()
                    ->imageEditor()
                    ->maxSize(8192) // 8 MB
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->required()
                    ->columnSpan(1),

                Forms\Components\SpatieMediaLibraryFileUpload::make('mobile')
                    ->label('Imagem Mobile')
                    ->helperText('Recomendado: 768×500px. Caso seja maior, será reduzida automaticamente para 768px de largura.')
                    ->collection('mobile')
                    ->image()
                    ->imageEditor()
                    ->maxSize(4096) // 4 MB
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->required()
                    ->columnSpan(1),
            ])->columns(2),

            Forms\Components\Section::make('Link e Exibição')->schema([
                Forms\Components\TextInput::make('link')
                    ->label('Link')
                    ->helperText('URL de destino ao clicar no banner. Deixe em branco para sem link.')
                    ->url()
                    ->maxLength(500)
                    ->columnSpan(2),

                Forms\Components\Toggle::make('open_in_new_tab')
                    ->label('Abrir em nova aba')
                    ->default(false)
                    ->inline(false)
                    ->columnSpan(1),

                Forms\Components\Toggle::make('active')
                    ->label('Ativo')
                    ->default(true)
                    ->inline(false)
                    ->columnSpan(1),
            ])->columns(4),

        ]);
    }

    // ── Tabela ────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('desktop')
                    ->label('Desktop')
                    ->collection('desktop')
                    ->conversion('desktop')
                    ->width(120)
                    ->height(40)
                    ->extraImgAttributes(['class' => 'rounded object-cover w-full h-full']),

                Tables\Columns\SpatieMediaLibraryImageColumn::make('mobile')
                    ->label('Mobile')
                    ->collection('mobile')
                    ->conversion('mobile')
                    ->width(60)
                    ->height(40)
                    ->extraImgAttributes(['class' => 'rounded object-cover w-full h-full']),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('link')
                    ->label('Link')
                    ->url(fn ($state) => $state)
                    ->openUrlInNewTab()
                    ->limit(40)
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('open_in_new_tab')
                    ->label('Nova aba')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-top-right-on-square')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('gray')
                    ->falseColor('gray'),

                Tables\Columns\ToggleColumn::make('active')
                    ->label('Ativo'),

                Tables\Columns\TextColumn::make('order')
                    ->label('Ordem')
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->reorderRecordsTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Reordenar'),
            )
            ->filters([
                Tables\Filters\TernaryFilter::make('active')->label('Status'),
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

    // ── Páginas ───────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBannersPrincipal::route('/'),
            'create' => Pages\CreateBannerPrincipal::route('/create'),
            'edit'   => Pages\EditBannerPrincipal::route('/{record}/edit'),
        ];
    }
}
