<?php

namespace App\Filament\Resources\ReturnRequestResource\Pages;

use App\Filament\Resources\ReturnRequestResource;
use App\Mail\ReturnRequestUpdated;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EditReturnRequest extends EditRecord
{
    protected static string $resource = ReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Só envia e-mail se houve alteração no status ou nas notas ao cliente
        if (! $record->wasChanged(['status', 'admin_notes'])) {
            return;
        }

        $email = $record->customer?->email;

        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->queue(new ReturnRequestUpdated($record));
        } catch (\Throwable $e) {
            Log::error('ReturnRequest: falha ao enfileirar e-mail de atualização', [
                'return_request_id' => $record->id,
                'error'             => $e->getMessage(),
            ]);
        }
    }
}
