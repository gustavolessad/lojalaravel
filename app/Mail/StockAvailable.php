<?php

namespace App\Mail;

use App\Models\StockNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockAvailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;

    public function __construct(public readonly StockNotification $notification) {}

    public function envelope(): Envelope
    {
        $productName = $this->notification->product?->name ?? 'Produto';

        return new Envelope(
            subject: "{$productName} voltou ao estoque!",
        );
    }

    public function content(): Content
    {
        $notification = $this->notification->load(['product', 'variant']);
        $product      = $notification->product;

        return new Content(
            markdown: 'emails.stock-available',
            with: [
                'notification' => $notification,
                'product'      => $product,
                'variant'      => $notification->variant,
                'url'          => $product ? route('product.show', $product->slug) : url('/'),
            ],
        );
    }
}
