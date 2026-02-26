<?php

namespace App\Filament\Resources\FreeShippingRuleResource\Pages;

use App\Filament\Resources\FreeShippingRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFreeShippingRule extends CreateRecord
{
    protected static string $resource = FreeShippingRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
