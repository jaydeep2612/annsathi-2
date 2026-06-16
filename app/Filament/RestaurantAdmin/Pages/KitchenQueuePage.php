<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Pages;

use Filament\Pages\Page;
use App\Models\KitchenQueue;
use App\Models\OrderItemKitchenStatus;
use App\Services\KitchenRoutingService;
use App\Services\OrderService;
use Filament\Notifications\Notification;

class KitchenQueuePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Kitchen Display (KDS)';
    protected static ?string $navigationGroup = 'POS & Orders';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.restaurant-admin.pages.kitchen-queue-page';

    public function getActiveTickets()
    {
        return KitchenQueue::whereIn('current_status', ['placed', 'preparing'])
            ->with(['order.customerSession.sessionable', 'kitchenStation', 'itemStatuses.orderItem'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function startPreparingTicket(int $ticketId): void
    {
        try {
            $ticket = KitchenQueue::findOrFail($ticketId);
            
            // Start preparing all items in the ticket
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
