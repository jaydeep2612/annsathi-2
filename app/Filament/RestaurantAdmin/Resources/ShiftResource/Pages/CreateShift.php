<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\ShiftResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ShiftResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShift extends CreateRecord
{
    protected static string $resource = ShiftResource::class;
}
