<?php

namespace App\Filament\SuperAdmin\Resources\ActivityResource\Pages;

use App\Filament\SuperAdmin\Resources\ActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
