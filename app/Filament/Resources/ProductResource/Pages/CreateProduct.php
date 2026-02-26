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
        $attrIds   = $this->data['attributes'] ?? [];
        $expandIds = array_map('intval', $this->data['expand_catalog_attributes'] ?? []);

        $pivotData = collect($attrIds)->mapWithKeys(fn ($id) => [
            (int) $id => ['expand_in_catalog' => in_array((int) $id, $expandIds)],
        ])->toArray();

        $this->record->attributes()->sync($pivotData);
    }
}
