<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
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
