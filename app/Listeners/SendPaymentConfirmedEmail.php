<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\PaymentConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmedEmail implements ShouldQueue
{
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        if (! $order->buyer_email || $order->buyer_email === '—') {
            return;
        }

        Mail::to($order->buyer_email)->queue(new PaymentConfirmed($order));
    }
}
