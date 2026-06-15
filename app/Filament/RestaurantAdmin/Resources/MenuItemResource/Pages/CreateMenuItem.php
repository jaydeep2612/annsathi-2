<?php

namespace App\Filament\RestaurantAdmin\Resources\MenuItemResource\Pages;

use App\Filament\RestaurantAdmin\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuItem extends CreateRecord
{
    protected static string $resource = MenuItemResource::class;
}
