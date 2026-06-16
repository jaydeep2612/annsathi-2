<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\ShiftResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShifts extends ListRecords
{
    protected static string $resource = ShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
