<?php

namespace App\Providers;

use App\Listeners\OptimizeOriginalImage;
use App\Services\Payment\Drivers\AsaasGateway;
use App\Services\Payment\PaymentManager;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function ($app) {
            $manager = new PaymentManager();
            $manager->registerGateway('asaas', $app->make(AsaasGateway::class));
            return $manager;
        });
    }

    public function boot(): void
    {
        // Otimiza original das imagens no upload (reduz peso no disco)
        Event::listen(MediaHasBeenAddedEvent::class, OptimizeOriginalImage::class);

        $addresses = fn (array $list) => array_map(fn ($a) => $a->getAddress(), $list);

        // Loga tentativa de envio
        Event::listen(MessageSending::class, function (MessageSending $event) use ($addresses) {
            $message = $event->message;
            Log::channel('email')->info('ENVIANDO e-mail', [
                'to'      => $addresses($message->getTo() ?? []),
                'subject' => $message->getSubject(),
                'from'    => $addresses($message->getFrom() ?? []),
            ]);
        });

        // Loga envio bem-sucedido
        Event::listen(MessageSent::class, function (MessageSent $event) use ($addresses) {
            $message = $event->message;
            Log::channel('email')->info('E-mail ENVIADO com sucesso', [
                'to'      => $addresses($message->getTo() ?? []),
                'subject' => $message->getSubject(),
            ]);
        });

        // Loga falhas de jobs na fila (inclui falhas de Mailable com ShouldQueue)
        Event::listen(JobFailed::class, function (JobFailed $event) {
            Log::channel('email')->error('JOB FALHOU na fila', [
                'job'       => $event->job->resolveName(),
                'exception' => $event->exception->getMessage(),
                'trace'     => $event->exception->getTraceAsString(),
            ]);
        });
    }
}
