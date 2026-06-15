<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Events\OrderCancelled;
use App\Services\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class UpdateInventoryListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(protected InventoryService $inventoryService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        try {
            if ($event instanceof OrderConfirmed) {
                // Ensure tenant context is bound
                app()->bind('tenant.restaurant_id', fn() => $event->order->restaurant_id);
                app()->bind('tenant.branch_id', fn() => $event->order->branch_id);

                $this->inventoryService->deductStockForOrder($event->order);
            } elseif ($event instanceof OrderCancelled) {
                // Ensure tenant context is bound
                app()->bind('tenant.restaurant_id', fn() => $event->order->restaurant_id);
                app()->bind('tenant.branch_id', fn() => $event->order->branch_id);

                $this->inventoryService->restoreStockForOrder($event->order);
            }
        } catch (\Exception $e) {
            Log::error("Inventory deduction/restoration listener failed: " . $e->getMessage());
        }
    }
}
