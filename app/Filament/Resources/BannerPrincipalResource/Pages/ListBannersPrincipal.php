<?php

namespace App\Filament\Resources\BannerPrincipalResource\Pages;

use App\Filament\Resources\BannerPrincipalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBannersPrincipal extends ListRecords
{
    protected static string $resource = BannerPrincipalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Novo banner'),
        ];
    }
}
