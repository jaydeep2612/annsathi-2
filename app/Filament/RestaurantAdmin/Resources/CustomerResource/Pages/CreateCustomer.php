<?php

namespace App\Filament\RestaurantAdmin\Resources\CustomerResource\Pages;

use App\Filament\RestaurantAdmin\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
