<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderEvents';
    protected static ?string $title       = 'Histórico do Pedido';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data/hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'order_created'          => 'Pedido criado',
                        'payment_attempted'      => 'Tentativa',
                        'payment_failed'         => 'Falhou',
                        'payment_confirmed'      => 'Confirmado',
                        'payment_method_changed' => 'Método alterado',
                        'order_shipped'          => 'Enviado',
                        'order_delivered'        => 'Entregue',
                        'order_cancelled'        => 'Cancelado',
                        default                  => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn ($state) => match ($state) {
                        'order_created'          => 'info',
                        'payment_attempted'      => 'warning',
                        'payment_failed'         => 'danger',
                        'payment_confirmed'      => 'success',
                        'payment_method_changed' => 'gray',
                        'order_shipped'          => 'info',
                        'order_delivered'        => 'success',
                        'order_cancelled'        => 'gray',
                        default                  => 'gray',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'asc')
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
