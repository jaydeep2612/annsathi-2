<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Pages;

use Filament\Pages\Page;
use App\Models\KitchenQueue;
use App\Models\KitchenStation;
use App\Models\OrderItemKitchenStatus;
use App\Services\KitchenRoutingService;
use Filament\Notifications\Notification;

class StationQueuePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-funnel';
    protected static ?string $navigationLabel = 'Station Display';
    protected static ?string $navigationGroup = 'POS & Orders';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.restaurant-admin.pages.station-queue-page';

    public ?int $selectedStationId = null;

    public function mount(): void
    {
        // Default to first active station if available
        $firstStation = KitchenStation::where('is_active', true)->first();
        $this->selectedStationId = $firstStation?->id;
    }

    public function getStations()
    {
        return KitchenStation::where('is_active', true)->get();
    }

    public function getActiveTickets()
    {
        if (!$this->selectedStationId) {
            return collect();
        }

        return KitchenQueue::where('kitchen_station_id', $this->selectedStationId)
            ->whereIn('current_status', ['placed', 'preparing'])
            ->with(['order.customerSession.sessionable', 'kitchenStation', 'itemStatuses.orderItem'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function startPreparingTicket(int $ticketId): void
    {
        try {
            $ticket = KitchenQueue::findOrFail($ticketId);
            
            foreach ($ticket->itemStatuses as $itemStatus) {
                if ($itemStatus->status === 'queued') {
                    app(KitchenRoutingService::class)->startPreparingItem($itemStatus->id, auth()->id() ?: 1);
                }
            }

            Notification::make()
                ->title('KDS Ticket Started')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Action Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function startPreparingItem(int $itemStatusId): void
    {
        try {
            app(KitchenRoutingService::class)->startPreparingItem($itemStatusId, auth()->id() ?: 1);
            Notification::make()
                ->title('Item Started')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Action Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function completeItem(int $itemStatusId): void
    {
        try {
            app(KitchenRoutingService::class)->completePreparingItem($itemStatusId);
            Notification::make()
                ->title('Item Marked Ready')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Action Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
