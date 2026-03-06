<?php

namespace App\Mail;

use App\Models\Sales\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnRequestUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;

    public function __construct(public readonly ReturnRequest $returnRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Atualização na sua solicitação #{$this->returnRequest->id} — " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.return-request-updated',
            with: [
                'returnRequest' => $this->returnRequest,
                'url'           => route('account.returns.index'),
            ],
        );
    }
}
