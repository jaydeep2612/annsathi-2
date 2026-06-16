<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\ShiftResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ShiftResource;
use Filament\Resources\Pages\EditRecord;

class EditShift extends EditRecord
{
    protected static string $resource = ShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
