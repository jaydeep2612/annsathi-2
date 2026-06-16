<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\RestaurantTableResource\Pages;

use App\Filament\RestaurantAdmin\Resources\RestaurantTableResource;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantTable extends EditRecord
{
    protected static string $resource = RestaurantTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
