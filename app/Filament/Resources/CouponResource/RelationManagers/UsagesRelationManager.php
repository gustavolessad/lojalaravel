<?php

namespace App\Filament\Resources\CouponResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class UsagesRelationManager extends RelationManager
{
    protected static string $relationship      = 'usages';
    protected static ?string $title            = 'Histórico de Usos';
    protected static ?string $modelLabel       = 'Uso';
    protected static ?string $pluralModelLabel = 'Usos';

    public function form(Form $form): Form
    {
        return $form->schema([]); // somente leitura
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Pedido')
                    ->url(fn ($record) => $record->order_id
                        ? route('filament.admin.resources.orders.edit', $record->order_id)
                        : null)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->placeholder('Guest'),

                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Desconto aplicado')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Usado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->headerActions([])  // somente leitura
            ->actions([])
            ->bulkActions([]);
    }
}
