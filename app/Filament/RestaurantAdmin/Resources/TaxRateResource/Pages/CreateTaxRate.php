<?php

namespace App\Filament\RestaurantAdmin\Resources\TaxRateResource\Pages;

use App\Filament\RestaurantAdmin\Resources\TaxRateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxRate extends CreateRecord
{
    protected static string $resource = TaxRateResource::class;
}
