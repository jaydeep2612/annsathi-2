<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\PrinterResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PrinterResource;
use Filament\Resources\Pages\EditRecord;

class EditPrinter extends EditRecord
{
    protected static string $resource = PrinterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
