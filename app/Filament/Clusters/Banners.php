<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Banners extends Cluster
{
    protected static ?string $navigationIcon  = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Banners';
    protected static ?string $navigationGroup = 'Aparência';
    protected static ?int    $navigationSort  = 1;
}
