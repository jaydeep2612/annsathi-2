<?php

namespace App\Filament\RestaurantAdmin\Resources\TaxGroupResource\Pages;

use App\Filament\RestaurantAdmin\Resources\TaxGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxGroup extends CreateRecord
{
    protected static string $resource = TaxGroupResource::class;
}
