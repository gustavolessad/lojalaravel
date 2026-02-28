<?php

use App\Jobs\SendAbandonedCartEmails;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Verifica carrinhos abandonados a cada hora
Schedule::job(new SendAbandonedCartEmails)->hourly();
