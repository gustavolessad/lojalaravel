<?php

namespace App\Providers;

use App\Events\CustomerRegistered;
use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Listeners\NotifyAdminNewOrder;
use App\Listeners\OptimizeOriginalImage;
use App\Listeners\SendCustomerWelcomeEmail;
use App\Listeners\SendOrderPlacedEmail;
use App\Listeners\SendPaymentConfirmedEmail;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ── Media ──────────────────────────────────────────────────────
        Event::listen(MediaHasBeenAddedEvent::class, OptimizeOriginalImage::class);

        // ── Domain Events ──────────────────────────────────────────────
        Event::listen(OrderCreated::class, SendOrderPlacedEmail::class);
        Event::listen(OrderCreated::class, NotifyAdminNewOrder::class);
        Event::listen(OrderPaid::class, SendPaymentConfirmedEmail::class);
        Event::listen(CustomerRegistered::class, SendCustomerWelcomeEmail::class);

        // ── Email Logging ──────────────────────────────────────────────
        $addresses = fn (array $list) => array_map(fn ($a) => $a->getAddress(), $list);

        Event::listen(MessageSending::class, function (MessageSending $event) use ($addresses) {
            $message = $event->message;
            Log::channel('email')->info('ENVIANDO e-mail', [
                'to'      => $addresses($message->getTo() ?? []),
                'subject' => $message->getSubject(),
                'from'    => $addresses($message->getFrom() ?? []),
            ]);
        });

        Event::listen(MessageSent::class, function (MessageSent $event) use ($addresses) {
            $message = $event->message;
            Log::channel('email')->info('E-mail ENVIADO com sucesso', [
                'to'      => $addresses($message->getTo() ?? []),
                'subject' => $message->getSubject(),
            ]);
        });

        Event::listen(JobFailed::class, function (JobFailed $event) {
            Log::channel('email')->error('JOB FALHOU na fila', [
                'job'       => $event->job->resolveName(),
                'exception' => $event->exception->getMessage(),
                'trace'     => $event->exception->getTraceAsString(),
            ]);
        });
    }
}
