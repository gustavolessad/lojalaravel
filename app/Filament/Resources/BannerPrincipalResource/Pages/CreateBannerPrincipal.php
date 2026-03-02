<?php

namespace App\Filament\Resources\BannerPrincipalResource\Pages;

use App\Filament\Resources\BannerPrincipalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBannerPrincipal extends CreateRecord
{
    protected static string $resource = BannerPrincipalResource::class;

    /** Define automaticamente o grupo como "principal" ao criar. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['group'] = 'principal';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
