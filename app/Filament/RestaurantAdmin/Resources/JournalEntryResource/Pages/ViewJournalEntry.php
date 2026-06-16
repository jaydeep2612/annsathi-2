<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\JournalEntryResource\Pages;

use App\Filament\RestaurantAdmin\Resources\JournalEntryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;
}
