<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Services\BillingService;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;

class GenerateInvoiceListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(protected BillingService $billingService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;

        try {
            // Find the paid payment for this order
            $payment = Payment::where('order_id', $order->id)
                ->where('status', 'paid')
                ->first();

            if ($payment) {
                // Ensure tenant context is bound before calling service
                app()->bind('tenant.restaurant_id', fn() => $order->restaurant_id);
                app()->bind('tenant.branch_id', fn() => $order->branch_id);

                $this->billingService->generateInvoice($order, $payment);
            }
        } catch (\Exception $e) {
            Log::error("Failed to auto-generate invoice for order {$order->id}: " . $e->getMessage());
        }
    }
}
