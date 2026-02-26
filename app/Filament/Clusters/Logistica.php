<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Logistica extends Cluster
{
    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Logística';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int    $navigationSort  = 10;
}
