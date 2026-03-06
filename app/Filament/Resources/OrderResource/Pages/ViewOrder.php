<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Sales\Order;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('mark_paid')
                ->label('Marcar Pago')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->payment_status === 'pending' && $this->record->status !== 'cancelled')
                ->action(function () {
                    $this->record->update([
                        'payment_status' => 'paid',
                        'status'         => 'processing',
                        'paid_at'        => now(),
                    ]);
                    $this->refreshFormData(['status', 'payment_status', 'paid_at']);
                }),

            Actions\Action::make('mark_shipped')
                ->label('Marcar Enviado')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['paid', 'processing']))
                ->action(function () {
                    $this->record->update([
                        'status'     => 'shipped',
                        'shipped_at' => now(),
                    ]);
                    $this->refreshFormData(['status', 'shipped_at']);
                }),

            Actions\Action::make('mark_delivered')
                ->label('Marcar Entregue')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'shipped')
                ->action(function () {
                    $this->record->update([
                        'status'       => 'delivered',
                        'delivered_at' => now(),
                    ]);
                    $this->refreshFormData(['status', 'delivered_at']);
                }),

            Actions\Action::make('cancel')
                ->label('Cancelar Pedido')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['pending', 'processing']))
                ->action(function () {
                    $this->record->update([
                        'status'       => 'cancelled',
                        'cancelled_at' => now(),
                    ]);
                    $this->refreshFormData(['status', 'cancelled_at']);
                }),
        ];
    }
}
