<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\PrinterRouteResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PrinterRouteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrinterRoutes extends ListRecords
{
    protected static string $resource = PrinterRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
