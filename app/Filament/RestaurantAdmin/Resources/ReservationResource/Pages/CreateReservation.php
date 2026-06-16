<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\ReservationResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ReservationResource;
use App\Domains\Reservations\Services\ReservationService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Exception;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $service = app(ReservationService::class);
            return $service->createReservation($data);
        } catch (Exception $e) {
            Notification::make()
                ->title('Reservation Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
