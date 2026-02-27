<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Pagamento extends Cluster
{
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Pagamento';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int    $navigationSort  = 11;
}
