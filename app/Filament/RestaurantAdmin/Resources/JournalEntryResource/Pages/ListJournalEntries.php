<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\JournalEntryResource\Pages;

use App\Filament\RestaurantAdmin\Resources\JournalEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
