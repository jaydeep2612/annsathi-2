<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\PrinterGroupResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PrinterGroupResource;
use Filament\Resources\Pages\EditRecord;

class EditPrinterGroup extends EditRecord
{
    protected static string $resource = PrinterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
