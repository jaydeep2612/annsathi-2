<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

// Events
use App\Events\OrderCompleted;
use App\Events\OrderConfirmed;
use App\Events\OrderCancelled;
use App\Events\LowStockAlert;
use App\Events\ApprovalRequested;
use App\Events\CreditNoteIssued;
use App\Events\ApprovalApproved;
use App\Events\ShiftClosed;

// Listeners
use App\Listeners\GenerateInvoiceListener;
use App\Listeners\UpdateInventoryListener;
use App\Listeners\BroadcastKitchenListener;
use App\Listeners\NotifyManagerListener;
use App\Listeners\CreateActivityLogListener;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Event Listeners
        Event::listen(OrderCompleted::class, GenerateInvoiceListener::class);
        Event::listen(OrderConfirmed::class, UpdateInventoryListener::class);
        Event::listen(OrderCancelled::class, UpdateInventoryListener::class);
        Event::listen(OrderConfirmed::class, BroadcastKitchenListener::class);
        Event::listen(LowStockAlert::class, NotifyManagerListener::class);
        Event::listen(ApprovalRequested::class, NotifyManagerListener::class);
        Event::listen(OrderCancelled::class, CreateActivityLogListener::class);
        Event::listen(CreditNoteIssued::class, CreateActivityLogListener::class);
        Event::listen(ApprovalApproved::class, CreateActivityLogListener::class);
        Event::listen(ShiftClosed::class, CreateActivityLogListener::class);

        // Profile queries and log queries taking longer than 500ms
        DB::listen(function ($query) {
            if ($query->time > 500) {
                Log::warning(sprintf(
                    "Slow Query Detected: %s [Time: %.2fms] [Bindings: %s]",
                    $query->sql,
                    $query->time,
                    json_encode($query->bindings)
                ));
            }
        });
    }
}
