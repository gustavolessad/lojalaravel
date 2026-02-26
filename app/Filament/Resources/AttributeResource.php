<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Models\Attribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'select' => 'Seleção',
                        'color'  => 'Cor',
                        'text'   => 'Texto',
                    ])
                    ->required()
                    ->default('select')
                    ->live(),
            ])->columns(2),

            Forms\Components\Section::make('Valores')->schema([
                Forms\Components\Repeater::make('values')
                    ->label('')
                    ->relationship()
                    ->schema(fn (Forms\Get $get) => [
                        Forms\Components\TextInput::make('value')
                            ->label('Valor')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('display_value')
                            ->label('Exibição')
                            ->maxLength(100)
                            ->placeholder('Igual ao valor'),

                        Forms\Components\ColorPicker::make('color_hex')
                            ->label('Cor')
                            ->visible(fn () => $get('../../type') === 'color'),
                    ])
                    ->columns(3)
                    ->addActionLabel('Adicionar valor')
                    ->reorderable('order')
                    ->cloneable()
                    ->collapsible(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'select' => 'Seleção',
                        'color'  => 'Cor',
                        'text'   => 'Texto',
                        default  => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'select' => 'primary',
                        'color'  => 'success',
                        'text'   => 'warning',
                        default  => 'gray',
                    }),

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
