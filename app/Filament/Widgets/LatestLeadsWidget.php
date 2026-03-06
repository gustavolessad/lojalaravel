<?php

namespace App\Filament\Widgets;

use App\Models\Marketing\NewsletterLead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLeadsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = 'Últimos leads (newsletter)';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(NewsletterLead::query()->latest()->limit(10))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->default('—')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->searchable(false),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->color('gray')
                    ->copyable()
                    ->searchable(false),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->default('—')
                    ->color('gray')
                    ->searchable(false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Captado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ]);
    }
}
