<?php

namespace App\Filament\RestaurantAdmin\Resources\ItemVariantGroupResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ItemVariantGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemVariantGroups extends ListRecords
{
    protected static string $resource = ItemVariantGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
