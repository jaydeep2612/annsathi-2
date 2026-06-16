<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\RestaurantTableResource\Pages;

use App\Filament\RestaurantAdmin\Resources\RestaurantTableResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRestaurantTable extends CreateRecord
{
    protected static string $resource = RestaurantTableResource::class;
}
