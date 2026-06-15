<?php

namespace App\Filament\RestaurantAdmin\Resources\TaxGroupResource\Pages;

use App\Filament\RestaurantAdmin\Resources\TaxGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxGroup extends EditRecord
{
    protected static string $resource = TaxGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
