<?php

namespace App\Filament\RestaurantAdmin\Resources\GroceryItemResource\Pages;

use App\Filament\RestaurantAdmin\Resources\GroceryItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGroceryItem extends EditRecord
{
    protected static string $resource = GroceryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
