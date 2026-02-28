<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title          = 'Dashboard';

    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Select::make('period')
                ->label('Período')
                ->options([
                    '7'      => 'Últimos 7 dias',
                    '14'     => 'Últimos 14 dias',
                    '30'     => 'Últimos 30 dias',
                    'month'  => 'Mês atual',
                    'custom' => 'Personalizado',
                ])
                ->default('30')
                ->live(),

            DatePicker::make('startDate')
                ->label('De')
                ->displayFormat('d/m/Y')
                ->visible(fn (Get $get) => $get('period') === 'custom')
                ->default(now()->subDays(30)->format('Y-m-d')),

            DatePicker::make('endDate')
                ->label('Até')
                ->displayFormat('d/m/Y')
                ->visible(fn (Get $get) => $get('period') === 'custom')
                ->default(now()->format('Y-m-d')),
        ]);
    }
}
