<?php

namespace App\Filament\Resources\NewsletterLeadResource\Pages;

use App\Filament\Resources\NewsletterLeadResource;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterLeads extends ListRecords
{
    protected static string $resource = NewsletterLeadResource::class;
}
