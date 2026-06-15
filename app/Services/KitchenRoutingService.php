<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\KitchenQueue;
use App\Models\OrderItemKitchenStatus;
use Illuminate\Support\Facades\DB;
use Exception;

class KitchenRoutingService
{
    /**
     * Route order items to their respective kitchen stations and queue them.
     */
    public function routeOrderToKitchen(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $items = $order->orderItems()->where('status', 'pending')->with('menuItem')->get();

            // Group items by kitchen station
            $grouped = $items->groupBy(function ($item) {
                return $item->menuItem?->kitchen_station_id ?: 'none';
            });

            foreach ($grouped as $stationId => $groupItems) {
                // If no kitchen station is bound, mark item as ready directly
                if ($stationId === 'none') {
                    foreach ($groupItems as $item) {
                        $item->update(['status' => 'ready']);
                    }
                    continue;
                }

                // Create a Kitchen Queue ticket for this station
                $ticket = KitchenQueue::create([
                    'order_id' => $order->id,
                    'kitchen_station_id' => $stationId,
                    'branch_id' => $order->branch_id,
                    'priority' => 'normal',
                    'current_status' => 'placed',
                ]);

                foreach ($groupItems as $item) {
                    // Update Order Item status to preparing or pending
                    $item->update(['status' => 'preparing']);

                    // Create detailed kitchen status
                    OrderItemKitchenStatus::create([
                        'order_item_id' => $item->id,
                        'kitchen_station_id' => $stationId,
                        'kitchen_queue_id' => $ticket->id,
                        'status' => 'queued',
                    ]);
                }
            }
        });
    }

    /**
     * Transition a specific kitchen queue item status.
     */
    public function startPreparingItem(int $orderItemKitchenStatusId, int $chefId): void
    {
        DB::transaction(function () use ($orderItemKitchenStatusId, $chefId) {
            $status = OrderItemKitchenStatus::findOrFail($orderItemKitchenStatusId);
            $status->update([
                'status' => 'preparing',
                'started_at' => now(),
            ]);

            $ticket = $status->kitchenQueue;
            if ($ticket->current_status === 'placed') {
                $ticket->update([
                    'current_status' => 'preparing',
                    'assigned_chef_id' => $chefId,
                    'acknowledged_at' => now(),
                    'started_at' => now(),
                ]);
            }
        });
    }

    /**
     * Mark a kitchen item as ready, checking if the whole ticket/order is ready.
     */
    public function completePreparingItem(int $orderItemKitchenStatusId): void
    {
        DB::transaction(function () use ($orderItemKitchenStatusId) {
            $status = OrderItemKitchenStatus::findOrFail($orderItemKitchenStatusId);
            $status->update([
                'status' => 'ready',
                'completed_at' => now(),
            ]);

            // Update parent OrderItem status
            $orderItem = $status->orderItem;
            $orderItem->update(['status' => 'ready']);

            $ticket = $status->kitchenQueue;
            
            // Check if all items in this kitchen ticket/queue are ready
            $remaining = OrderItemKitchenStatus::where('kitchen_queue_id', $ticket->id)
                ->where('status', '!=', 'ready')
                ->count();

            if ($remaining === 0) {
                $ticket->update([
                    'current_status' => 'ready',
                    'completed_at' => now(),
                ]);
            }

            // Check if all items in the parent Order are ready
            $order = $orderItem->order;
            $remainingOrderItems = OrderItem::where('order_id', $order->id)
                ->where('status', '!=', 'ready')
                ->where('status', '!=', 'served')
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($remainingOrderItems === 0) {
                // Use OrderService to mark order ready (triggers events)
                app(OrderService::class)->markReady($order->id);
            }
        });
    }
}
