<?php

namespace App\Filament\Resources\CarrierResource\Pages;

use App\Filament\Resources\CarrierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCarrier extends CreateRecord
{
    protected static string $resource = CarrierResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
