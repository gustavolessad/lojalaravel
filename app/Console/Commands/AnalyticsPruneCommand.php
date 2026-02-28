<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class AnalyticsPruneCommand extends Command
{
    protected $signature   = 'analytics:prune {--months=13 : Remover eventos mais antigos que N meses}';
    protected $description = 'Remove eventos de analytics mais antigos que o período configurado';

    public function handle(AnalyticsService $analytics): int
    {
        $months  = (int) $this->option('months');
        $deleted = $analytics->pruneOlderThan($months);

        $this->info("analytics:prune — {$deleted} eventos removidos (mais de {$months} meses).");

        return self::SUCCESS;
    }
}
