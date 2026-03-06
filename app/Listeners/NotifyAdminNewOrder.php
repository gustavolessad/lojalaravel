<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\User;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyAdminNewOrder implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        try {
            FilamentNotification::make()
                ->title('Novo pedido recebido')
                ->body("Pedido #{$order->order_number} de {$order->buyer_name} — R$ " . number_format($order->total, 2, ',', '.'))
                ->icon('heroicon-o-shopping-bag')
                ->iconColor('success')
                ->actions([
                    NotificationAction::make('ver')
                        ->label('Ver pedido')
                        ->button()
                        ->url(\App\Filament\Resources\OrderResource::getUrl('view', ['record' => $order->id]))
                        ->extraAttributes(function () use ($order) {
                            $url = \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $order->id]);
                            return ['x-on:click.prevent' => "markAsRead(); setTimeout(() => { window.location.href = '{$url}'; }, 300)"];
                        }),
                ])
                ->sendToDatabase(User::all());
        } catch (\Throwable $e) {
            Log::warning('NotifyAdminNewOrder: falha ao enviar notificação', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
