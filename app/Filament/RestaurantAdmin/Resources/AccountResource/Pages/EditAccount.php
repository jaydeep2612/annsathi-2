<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\AccountResource\Pages;

use App\Filament\RestaurantAdmin\Resources\AccountResource;
use Filament\Resources\Pages\EditRecord;

class EditAccount extends EditRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
