<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title       = 'Tentativas de Pagamento';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data/hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('method')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pix'         => 'PIX',
                        'credit_card' => 'Cartão',
                        'boleto'      => 'Boleto',
                        default       => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pix'         => 'success',
                        'credit_card' => 'info',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'   => 'Aguardando',
                        'confirmed' => 'Confirmado',
                        'failed'    => 'Falhou',
                        'refunded'  => 'Reembolsado',
                        default     => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending'   => 'warning',
                        'confirmed' => 'success',
                        'failed'    => 'danger',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('installments')
                    ->label('Parcelas')
                    ->formatStateUsing(fn ($state) => $state > 1 ? "{$state}×" : '1×'),

                Tables\Columns\TextColumn::make('gateway_payment_id')
                    ->label('ID Gateway')
                    ->copyable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Erro')
                    ->limit(60)
                    ->placeholder('—')
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('confirmed_at')
                    ->label('Confirmado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
