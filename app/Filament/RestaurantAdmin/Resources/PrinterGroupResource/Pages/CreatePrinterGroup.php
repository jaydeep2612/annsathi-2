<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\PrinterGroupResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PrinterGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrinterGroup extends CreateRecord
{
    protected static string $resource = PrinterGroupResource::class;
}
