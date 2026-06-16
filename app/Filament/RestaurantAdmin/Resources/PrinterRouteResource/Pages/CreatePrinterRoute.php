<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\PrinterRouteResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PrinterRouteResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrinterRoute extends CreateRecord
{
    protected static string $resource = PrinterRouteResource::class;
}
