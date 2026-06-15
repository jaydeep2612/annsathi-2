<?php

namespace App\Filament\RestaurantAdmin\Resources\TaxGroupResource\Pages;

use App\Filament\RestaurantAdmin\Resources\TaxGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxGroups extends ListRecords
{
    protected static string $resource = TaxGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
