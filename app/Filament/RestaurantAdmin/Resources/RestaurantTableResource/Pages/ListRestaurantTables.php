<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\RestaurantTableResource\Pages;

use App\Filament\RestaurantAdmin\Resources\RestaurantTableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantTables extends ListRecords
{
    protected static string $resource = RestaurantTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
