<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\PrinterRouteResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PrinterRouteResource;
use Filament\Resources\Pages\EditRecord;

class EditPrinterRoute extends EditRecord
{
    protected static string $resource = PrinterRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
