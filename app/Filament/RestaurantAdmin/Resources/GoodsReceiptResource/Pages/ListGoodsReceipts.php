<?php

namespace App\Filament\RestaurantAdmin\Resources\GoodsReceiptResource\Pages;

use App\Filament\RestaurantAdmin\Resources\GoodsReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGoodsReceipts extends ListRecords
{
    protected static string $resource = GoodsReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
