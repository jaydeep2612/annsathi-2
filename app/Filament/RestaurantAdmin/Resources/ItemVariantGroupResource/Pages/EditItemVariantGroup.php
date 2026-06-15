<?php

namespace App\Filament\RestaurantAdmin\Resources\ItemVariantGroupResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ItemVariantGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemVariantGroup extends EditRecord
{
    protected static string $resource = ItemVariantGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
