<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\OrderResource\Pages;

use App\Filament\RestaurantAdmin\Resources\OrderResource;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
