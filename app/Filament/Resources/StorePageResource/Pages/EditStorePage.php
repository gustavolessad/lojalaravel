<?php

namespace App\Filament\Resources\StorePageResource\Pages;

use App\Filament\Resources\StorePageResource;
use Filament\Resources\Pages\EditRecord;

class EditStorePage extends EditRecord
{
    protected static string $resource = StorePageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->visible(fn () => ! $this->record->is_system),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return $this->record->title;
    }
}
