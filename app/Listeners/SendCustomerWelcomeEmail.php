<?php

namespace App\Listeners;

use App\Events\CustomerRegistered;
use App\Mail\CustomerWelcome;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCustomerWelcomeEmail implements ShouldQueue
{
    public function handle(CustomerRegistered $event): void
    {
        $customer = $event->customer;

        try {
            Log::channel('email')->info('Event: disparando CustomerWelcome', ['email' => $customer->email]);
            Mail::to($customer->email)->send(new CustomerWelcome($customer));
            Log::channel('email')->info('Event: CustomerWelcome enviado com sucesso', ['email' => $customer->email]);
        } catch (\Throwable $e) {
            Log::channel('email')->error('Event: FALHA ao enviar CustomerWelcome', [
                'email'     => $customer->email,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
