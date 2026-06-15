<?php

namespace App\Filament\RestaurantAdmin\Resources\TaxRateResource\Pages;

use App\Filament\RestaurantAdmin\Resources\TaxRateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxRate extends EditRecord
{
    protected static string $resource = TaxRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
