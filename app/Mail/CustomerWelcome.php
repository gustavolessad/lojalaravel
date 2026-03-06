<?php

namespace App\Mail;

use App\Models\Customer\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerWelcome extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;

    public function __construct(public readonly Customer $customer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo(a) à ' . config('app.name') . '!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customer-welcome',
            with: [
                'customer'    => $this->customer,
                'accountUrl'  => route('account.dashboard'),
                'storeUrl'    => url('/'),
            ],
        );
    }
}
