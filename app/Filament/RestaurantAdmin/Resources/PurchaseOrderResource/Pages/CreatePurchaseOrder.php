<?php

namespace App\Filament\RestaurantAdmin\Resources\PurchaseOrderResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;
        foreach ($data['items'] ?? [] as $item) {
            $total += ((float) ($item['ordered_quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0));
        }
        $data['total_amount'] = $total;
        $data['ordered_by'] = auth()->id();
        return $data;
    }
}
