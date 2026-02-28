<?php

namespace App\Filament\Resources\StorePageResource\Pages;

use App\Filament\Resources\StorePageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStorePages extends ListRecords
{
    protected static string $resource = StorePageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nova página'),
        ];
    }
}
