<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\AccountResource\Pages;

use App\Filament\RestaurantAdmin\Resources\AccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccount extends CreateRecord
{
    protected static string $resource = AccountResource::class;
}
