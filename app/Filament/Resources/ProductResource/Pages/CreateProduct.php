<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $this->syncExpandAttributes();
    }

    private function syncExpandAttributes(): void
    {
        $attrIds  = $this->data['attributes'] ?? [];
        $expandId = (int) ($this->data['expand_catalog_attributes'] ?? 0);

        $pivotData = collect($attrIds)->mapWithKeys(fn ($id) => [
            (int) $id => ['expand_in_catalog' => $expandId > 0 && (int) $id === $expandId],
        ])->toArray();

        $this->record->attributes()->sync($pivotData);
    }
}
