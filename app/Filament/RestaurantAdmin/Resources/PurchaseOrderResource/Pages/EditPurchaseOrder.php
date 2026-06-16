<?php

namespace App\Filament\RestaurantAdmin\Resources\PurchaseOrderResource\Pages;

use App\Filament\RestaurantAdmin\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $total = 0;
        foreach ($data['items'] ?? [] as $item) {
            $total += ((float) ($item['ordered_quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0));
        }
        $data['total_amount'] = $total;
        return $data;
    }
}
