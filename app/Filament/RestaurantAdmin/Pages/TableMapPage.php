<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Pages;

use Filament\Pages\Page;
use App\Models\RestaurantTable;
use App\Models\CustomerSession;
use App\Services\SessionService;
use App\Domains\Reservations\Services\ReservationService;
use App\Models\Reservation;
use App\Models\Shift;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Carbon\Carbon;

class TableMapPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Table Map';
    protected static ?string $navigationGroup = 'Seating & Reservations';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.restaurant-admin.pages.table-map-page';

    public function getTables()
    {
        return RestaurantTable::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function getActiveSession(RestaurantTable $table)
    {
        return $table->sessions()->where('status', 'active')->first();
    }

    public function getTableReservation(RestaurantTable $table)
    {
        // Get the closest confirmed/pending reservation for today
        return Reservation::where('restaurant_table_id', $table->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('reservation_time', Carbon::today())
            ->orderBy('reservation_time', 'asc')
            ->first();
    }

    public function markAvailable(int $tableId): void
    {
        $table = RestaurantTable::findOrFail($tableId);
        $table->update(['status' => 'available']);

        Notification::make()
            ->title("Table {$table->name} is now available.")
            ->success()
            ->send();
    }

    public function markCleaning(int $tableId): void
    {
        $table = RestaurantTable::findOrFail($tableId);
        $table->update(['status' => 'cleaning']);

        Notification::make()
            ->title("Table {$table->name} is being cleaned.")
            ->info()
            ->send();
    }

    public function seatWalkIn(int $tableId, string $customerName, ?string $customerPhone = null): void
    {
        try {
            $sessionService = app(SessionService::class);
            $shift = Shift::where('status', 'active')->first();

            $session = $sessionService->startSession([
                'session_type' => 'table',
                'sessionable_id' => $tableId,
                'customer_name' => $customerName ?: 'Walk-in Guest',
                'customer_phone' => $customerPhone,
                'shift_id' => $shift?->id,
            ]);

            Notification::make()
                ->title("Walk-in seated successfully.")
                ->body("Session token: {$session->session_token}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title("Seating failed")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function seatReservation(int $reservationId): void
    {
        try {
            $service = app(ReservationService::class);
            $shift = Shift::where('status', 'active')->first();
            $service->seatReservation($reservationId, $shift?->id);

            Notification::make()
                ->title("Reservation guest seated.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title("Seating failed")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
