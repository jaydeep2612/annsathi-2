<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Resources\OrderResource\Pages;

use App\Filament\RestaurantAdmin\Resources\OrderResource;
use App\Services\OrderService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Exception;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $service = app(OrderService::class);
            return $service->createOrder($data);
        } catch (Exception $e) {
            Notification::make()
                ->title('Order Creation Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
