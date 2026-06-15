<?php

namespace App\Filament\RestaurantAdmin\Resources\CategoryResource\Pages;

use App\Filament\RestaurantAdmin\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
