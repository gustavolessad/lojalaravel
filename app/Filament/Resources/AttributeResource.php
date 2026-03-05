<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Models\Attribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Atributo';

    protected static ?string $pluralModelLabel = 'Atributos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                Forms\Components\TextInput::make('order')
                    ->label('Ordem')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('Menor número aparece primeiro.')
                    ->columnSpan(1),
            ])->columns(3),

            Forms\Components\Section::make('Valores')->schema([
                Forms\Components\Repeater::make('values')
                    ->label('')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Valor')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('display_value')
                            ->label('Exibição')
                            ->maxLength(100)
                            ->placeholder('Igual ao valor'),

                        Forms\Components\TextInput::make('order')
                            ->label('Ordem')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\SpatieMediaLibraryFileUpload::make('icon')
                            ->label('Ícone')
                            ->collection('icon')
                            ->image()
                            ->imageEditor()
                            ->helperText('100×100px, usado nos botões de variação.')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record): string {
                                $attrSlug = Str::slug($record?->attribute?->name ?? 'atributo');
                                $valueSlug = Str::slug($record?->value ?? 'valor');
                                return $attrSlug . '-' . $valueSlug . '-' . date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 5) . '.jpg';
                            }),
                    ])
                    ->columns(4)
                    ->addActionLabel('Adicionar valor')
                    ->cloneable()
                    ->collapsible(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Ordem')
                    ->sortable()
                    ->width('80px'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('values_count')
                    ->label('Valores')
                    ->counts('values')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index'  => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit'   => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
