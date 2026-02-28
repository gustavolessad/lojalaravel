<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Geral extends Cluster
{
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Geral';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int    $navigationSort  = 9;
}
