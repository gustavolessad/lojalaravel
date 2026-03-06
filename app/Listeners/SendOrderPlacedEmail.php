<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedEmail implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if (! $order->buyer_email || $order->buyer_email === '—') {
            return;
        }

        try {
            Mail::to($order->buyer_email)->queue(new OrderPlaced($order));
        } catch (\Throwable $e) {
            Log::error('SendOrderPlacedEmail: falha ao enfileirar', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
