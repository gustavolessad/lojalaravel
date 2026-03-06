<?php

namespace App\Mail;

use App\Models\Sales\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pedido #{$this->order->order_number} recebido!",
        );
    }

    public function content(): Content
    {
        $order = $this->order->load('items');

        return new Content(
            markdown: 'emails.order-placed',
            with: [
                'order' => $order,
                'url'   => route('order.confirmation', $order->order_number),
            ],
        );
    }
}
