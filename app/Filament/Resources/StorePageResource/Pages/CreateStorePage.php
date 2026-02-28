<?php

namespace App\Filament\Resources\StorePageResource\Pages;

use App\Filament\Resources\StorePageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStorePage extends CreateRecord
{
    protected static string $resource = StorePageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_system'] = false;
        return $data;
    }
}
