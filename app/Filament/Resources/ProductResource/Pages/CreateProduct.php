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
        $this->syncCharacteristics();
    }

    private function syncCharacteristics(): void
    {
        $valueIds = collect($this->data)
            ->filter(fn ($v, $k) => str_starts_with((string) $k, 'char_') && is_array($v))
            ->flatMap(fn ($ids) => $ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->toArray();

        $this->record->characteristicValues()->sync($valueIds);
    }

    private function syncExpandAttributes(): void
    {
        $attrIds      = $this->data['attributes'] ?? [];
        $expandId     = (int) ($this->data['expand_catalog_attributes'] ?? 0);
        $imageAttrId  = (int) ($this->data['variant_image_attribute'] ?? 0);
        $imageType    = $this->data['variant_image_type'] ?? 'attribute_icon';

        $pivotData = collect($attrIds)->mapWithKeys(fn ($id) => [
            (int) $id => [
                'expand_in_catalog' => $expandId > 0 && (int) $id === $expandId,
                'variant_display'   => $imageAttrId > 0 && (int) $id === $imageAttrId ? $imageType : 'text',
            ],
        ])->toArray();

        $this->record->attributes()->sync($pivotData);
    }
}
