<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestCustomersWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = 'Novos clientes';

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()->latest()->limit(5)
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->url(fn (Customer $record) => \App\Filament\Resources\CustomerResource::getUrl('edit', ['record' => $record]))
                    ->color('primary')
                    ->limit(24),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'pj' ? 'PJ' : 'PF')
                    ->color(fn (string $state): string => $state === 'pj' ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->limit(26)
                    ->tooltip(fn (Customer $record) => $record->email)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->since()
                    ->tooltip(fn (Customer $record) => $record->created_at->format('d/m/Y H:i')),
            ]);
    }
}
