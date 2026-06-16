<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\ReservationResource\Pages;

use App\Filament\RestaurantAdmin\Resources\ReservationResource;
use App\Domains\Reservations\Services\ReservationService;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Exception;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $service = app(ReservationService::class);
            return $service->updateReservation($record->id, $data);
        } catch (Exception $e) {
            Notification::make()
                ->title('Update Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
