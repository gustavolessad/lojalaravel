<?php

namespace App\Mail;

use App\Models\Sales\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pagamento confirmado — Pedido #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-confirmed',
            with: [
                'order' => $this->order,
                'url'   => route('order.confirmation', $this->order->order_number),
            ],
        );
    }
}
