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

// Remove eventos de analytics com mais de 13 meses (todo dia 1 às 3h)
Schedule::command('analytics:prune')->monthlyOn(1, '03:00');
