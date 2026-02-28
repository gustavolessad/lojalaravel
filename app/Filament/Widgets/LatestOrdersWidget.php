<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Últimos pedidos';

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(6)
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('#')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->url(fn (Order $record) => \App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $record]))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('shipping_name')
                    ->label('Cliente')
                    ->limit(22)
                    ->tooltip(fn (Order $record) => $record->shipping_name),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('BRL')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid'     => 'Pago',
                        'pending'  => 'Pendente',
                        'refunded' => 'Estornado',
                        'failed'   => 'Falhou',
                        default    => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'paid'     => 'success',
                        'pending'  => 'warning',
                        'refunded' => 'gray',
                        'failed'   => 'danger',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->since()
                    ->tooltip(fn (Order $record) => $record->created_at->format('d/m/Y H:i')),
            ]);
    }
}
