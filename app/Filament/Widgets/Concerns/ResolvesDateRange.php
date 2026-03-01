<?php

namespace App\Filament\Widgets\Concerns;

use Carbon\Carbon;

trait ResolvesDateRange
{
    protected function getDateRange(): array
    {
        $period = $this->filters['period'] ?? '30';

        return match ($period) {
            '7'      => [now()->subDays(7)->startOfDay(),   now()->endOfDay()],
            '14'     => [now()->subDays(14)->startOfDay(),  now()->endOfDay()],
            'month'  => [now()->startOfMonth()->startOfDay(), now()->endOfDay()],
            'custom' => [
                Carbon::parse($this->filters['startDate'] ?? now()->subDays(30))->startOfDay(),
                Carbon::parse($this->filters['endDate']   ?? now())->endOfDay(),
            ],
            default  => [now()->subDays(30)->startOfDay(),  now()->endOfDay()],
        };
    }
}
