<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Estatisticas extends Cluster
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Estatísticas';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int    $navigationSort  = 5;
}
