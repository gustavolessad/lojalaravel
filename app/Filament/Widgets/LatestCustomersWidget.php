<?php

namespace App\Filament\Widgets;

use App\Models\Customer\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestCustomersWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Últimos clientes cadastrados';

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(Customer::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->url(fn (Customer $r) => \App\Filament\Resources\CustomerResource::getUrl('edit', ['record' => $r]))
                    ->color('primary')
                    ->searchable(false),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'pj' ? 'PJ' : 'PF')
                    ->color(fn (string $state) => $state === 'pj' ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->color('gray')
                    ->searchable(false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastrado')
                    ->since()
                    ->tooltip(fn (Customer $r) => $r->created_at->format('d/m/Y H:i')),
            ]);
    }
}
