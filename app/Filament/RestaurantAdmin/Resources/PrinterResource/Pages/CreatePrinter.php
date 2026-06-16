<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\PrinterResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PrinterResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrinter extends CreateRecord
{
    protected static string $resource = PrinterResource::class;
}
