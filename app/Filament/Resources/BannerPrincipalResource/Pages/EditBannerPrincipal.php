<?php

namespace App\Filament\Resources\BannerPrincipalResource\Pages;

use App\Filament\Resources\BannerPrincipalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBannerPrincipal extends EditRecord
{
    protected static string $resource = BannerPrincipalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
