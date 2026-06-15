<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Services\KitchenRoutingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class BroadcastKitchenListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(protected KitchenRoutingService $kitchenRoutingService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderConfirmed $event): void
    {
        $order = $event->order;

        try {
            // Ensure tenant context is bound
            app()->bind('tenant.restaurant_id', fn() => $order->restaurant_id);
            app()->bind('tenant.branch_id', fn() => $order->branch_id);

            // Queue tickets and update items
            $this->kitchenRoutingService->routeOrderToKitchen($order);

            // Trigger websocket broadcasts (Reverb setup will listen to ticket changes)
        } catch (\Exception $e) {
            Log::error("Failed to route order {$order->id} to kitchen: " . $e->getMessage());
        }
    }
}
