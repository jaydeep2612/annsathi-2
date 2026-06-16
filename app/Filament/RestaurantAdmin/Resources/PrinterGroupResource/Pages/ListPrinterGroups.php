<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\PrinterGroupResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PrinterGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrinterGroups extends ListRecords
{
    protected static string $resource = PrinterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
