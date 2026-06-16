<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\ReservationResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
