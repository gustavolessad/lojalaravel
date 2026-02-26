<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Logistica;
use App\Filament\Resources\CarrierResource\Pages;
use App\Filament\Resources\CarrierResource\RelationManagers;
use App\Models\Carrier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarrierResource extends Resource
{
    protected static ?string $model = Carrier::class;

    protected static ?string $cluster          = Logistica::class;
    protected static ?string $navigationIcon  = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Hub de Frete';
    protected static ?string $modelLabel      = 'Transportadora';
    protected static ?string $pluralModelLabel = 'Transportadoras';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Transportadora')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(100)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('active')
                        ->label('Ativa')
                        ->helperText('Apenas transportadoras ativas aparecem no checkout.')
                        ->default(true),
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

                Tables\Columns\TextColumn::make('rates_count')
                    ->label('Faixas de CEP')
                    ->counts('rates')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('active')
                    ->label('Ativa')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->trueLabel('Apenas ativas')
                    ->falseLabel('Apenas inativas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCarriers::route('/'),
            'create' => Pages\CreateCarrier::route('/create'),
            'edit'   => Pages\EditCarrier::route('/{record}/edit'),
        ];
    }
}
