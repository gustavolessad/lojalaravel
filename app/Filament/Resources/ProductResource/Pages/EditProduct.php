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
            Actions\Action::make('ver_produto')
                ->label('Ver produto')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('product.show', $this->record->slug))
                ->openUrlInNewTab(),

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
