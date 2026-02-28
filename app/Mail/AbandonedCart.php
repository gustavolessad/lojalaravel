<?php

namespace App\Mail;

use App\Models\Cart;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCart extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly Customer $customer,
        public readonly Cart     $cart,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Você deixou itens no carrinho!',
        );
    }

    public function content(): Content
    {
        $cart = $this->cart->load('items.product', 'items.variant');

        return new Content(
            markdown: 'emails.abandoned-cart',
            with: [
                'customer' => $this->customer,
                'cart'     => $cart,
                'url'      => route('cart.index'),
            ],
        );
    }
}
