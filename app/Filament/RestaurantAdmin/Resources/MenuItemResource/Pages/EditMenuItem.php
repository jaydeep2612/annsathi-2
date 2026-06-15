<?php

namespace App\Filament\RestaurantAdmin\Resources\MenuItemResource\Pages;

use App\Filament\RestaurantAdmin\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMenuItem extends EditRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
