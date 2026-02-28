<?php

namespace App\Filament\Resources\StockNotificationResource\Pages;

use App\Filament\Resources\StockNotificationResource;
use Filament\Resources\Pages\ListRecords;

class ListStockNotifications extends ListRecords
{
    protected static string $resource = StockNotificationResource::class;

    public function mount(): void
    {
        parent::mount();
        cache(['admin_stock_viewed_' . auth()->id() => now()], now()->addDays(30));
    }
}
