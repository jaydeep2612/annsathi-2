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

        // Register Generic policies for models
        $mappings = [
            \App\Models\Branch::class => 'manage_branches',
            \App\Models\User::class => 'manage_users',
            \Spatie\Permission\Models\Role::class => 'manage_users',
            \App\Models\MenuItem::class => 'manage_menu',
            \App\Models\Category::class => 'manage_menu',
            \App\Models\ItemVariantGroup::class => 'manage_menu',
            \App\Models\ItemVariant::class => 'manage_menu',
            \App\Models\Recipe::class => 'manage_menu',
            \App\Models\RecipeVersion::class => 'manage_menu',
            \App\Models\GroceryItem::class => 'manage_inventory',
            \App\Models\Warehouse::class => 'manage_inventory',
            \App\Models\PurchaseOrder::class => 'manage_inventory',
            \App\Models\GoodsReceipt::class => 'manage_inventory',
            \App\Models\Supplier::class => 'manage_suppliers',
            \App\Models\SupplierLedger::class => 'manage_suppliers',
            \App\Models\Order::class => 'place_manual_orders',
            \App\Models\Reservation::class => 'place_manual_orders',
            \App\Models\Refund::class => 'approve_refunds',
            \App\Models\ApprovalRequest::class => 'approve_refunds',
            \App\Models\Shift::class => 'manage_shifts',
            \App\Models\CashDrawer::class => 'manage_shifts',
            \App\Models\CashMovement::class => 'manage_shifts',
            \App\Models\Printer::class => 'manage_settings',
            \App\Models\PrinterGroup::class => 'manage_settings',
            \App\Models\PrinterRoute::class => 'manage_settings',
            \App\Models\Restaurant::class => 'manage_settings',
            \App\Models\RestaurantTable::class => 'manage_settings',
            \App\Models\TaxGroup::class => 'manage_settings',
            \App\Models\TaxRate::class => 'manage_settings',
            \App\Models\Account::class => 'manage_settings',
            \App\Models\JournalEntry::class => 'manage_settings',
        ];

        foreach (array_keys($mappings) as $model) {
            \Illuminate\Support\Facades\Gate::policy($model, \App\Policies\GenericPolicy::class);
        }

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
