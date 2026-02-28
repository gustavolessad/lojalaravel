<?php

namespace App\Filament\Resources\NewsletterLeadResource\Pages;

use App\Filament\Resources\NewsletterLeadResource;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterLeads extends ListRecords
{
    protected static string $resource = NewsletterLeadResource::class;

    public function mount(): void
    {
        parent::mount();
        cache(['admin_newsletter_viewed_' . auth()->id() => now()], now()->addDays(30));
    }
}
