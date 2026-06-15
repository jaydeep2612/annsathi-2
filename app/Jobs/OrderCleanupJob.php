<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class OrderCleanupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(OrderService $orderService): void
    {
        Log::info("Running OrderCleanupJob...");

        // Fetch pending orders older than 24 hours
        $staleOrders = Order::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        foreach ($staleOrders as $order) {
            try {
                // Bind tenant context
                app()->bind('tenant.restaurant_id', fn() => $order->restaurant_id);
                app()->bind('tenant.branch_id', fn() => $order->branch_id);

                $orderService->cancelOrder($order->id, 'Auto-cancelled by system stale order cleanup.');
                Log::info("Auto-cancelled stale pending order ID: {$order->id}");
            } catch (\Exception $e) {
                Log::error("Failed to auto-cancel order ID {$order->id}: " . $e->getMessage());
            }
        }
    }
}
