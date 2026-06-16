<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\AccountResource\Pages;

use App\Filament\RestaurantAdmin\Resources\AccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
