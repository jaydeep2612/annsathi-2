<?php

namespace App\Filament\RestaurantAdmin\Resources\GroceryItemResource\Pages;

use App\Filament\RestaurantAdmin\Resources\GroceryItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGroceryItem extends CreateRecord
{
    protected static string $resource = GroceryItemResource::class;
}
