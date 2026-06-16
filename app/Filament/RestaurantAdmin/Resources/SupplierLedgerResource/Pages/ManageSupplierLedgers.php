<?php

namespace App\Filament\RestaurantAdmin\Resources\SupplierLedgerResource\Pages;

use App\Filament\RestaurantAdmin\Resources\SupplierLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSupplierLedgers extends ManageRecords
{
    protected static string $resource = SupplierLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
