<?php

namespace App\Filament\Resources\ReturnRequestResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Produtos Solicitados';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orderItem.product_name')
                    ->label('Produto')
                    ->description(fn ($record) => $record->orderItem?->variant_label),

                Tables\Columns\TextColumn::make('orderItem.sku')
                    ->label('SKU')
                    ->fontFamily('mono')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qtd. solicitada')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('orderItem.quantity')
                    ->label('Qtd. no pedido')
                    ->alignCenter()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('orderItem.unit_price')
                    ->label('Preço unit.')
                    ->money('BRL'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
