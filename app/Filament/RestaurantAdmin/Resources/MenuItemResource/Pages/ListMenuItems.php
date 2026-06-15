<?php

namespace App\Filament\RestaurantAdmin\Resources\MenuItemResource\Pages;

use App\Filament\RestaurantAdmin\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenuItems extends ListRecords
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
