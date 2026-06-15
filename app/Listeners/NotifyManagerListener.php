<?php

namespace App\Listeners;

use App\Events\LowStockAlert;
use App\Events\ApprovalRequested;
use App\Models\NotificationsLog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class NotifyManagerListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        try {
            if ($event instanceof LowStockAlert) {
                $item = $event->groceryItem;
                
                // Find all managers/admins of the restaurant to alert
                $managers = User::where('restaurant_id', $item->restaurant_id)
                    ->role(['restaurant_admin', 'branch_admin', 'manager'])
                    ->get();

                foreach ($managers as $manager) {
                    NotificationsLog::create([
                        'restaurant_id' => $item->restaurant_id,
                        'branch_id' => $item->branch_id,
                        'user_id' => $manager->id,
                        'type' => 'warning',
                        'title' => 'Low Stock Warning',
                        'body' => "Raw ingredient '{$item->name}' stock level is low ({$event->quantity} remaining). Reorder is recommended.",
                        'data' => [
                            'grocery_item_id' => $item->id,
                            'sku' => $item->sku,
                            'current_stock' => $event->quantity,
                        ],
                    ]);
                }
            } elseif ($event instanceof ApprovalRequested) {
                $req = $event->approvalRequest;

                // Find branch managers / restaurant admins
                $managers = User::where('restaurant_id', $req->restaurant_id)
                    ->role(['restaurant_admin', 'branch_admin', 'manager'])
                    ->get();

                foreach ($managers as $manager) {
                    NotificationsLog::create([
                        'restaurant_id' => $req->restaurant_id,
                        'branch_id' => $req->branch_id,
                        'user_id' => $manager->id,
                        'type' => 'alert',
                        'title' => 'Manager Override Required',
                        'body' => "Approval requested for action '{$req->action}' by user ID {$req->requested_by}. Reason: {$req->reason}",
                        'data' => [
                            'approval_request_id' => $req->id,
                            'action' => $req->action,
                            'entity_type' => $req->entity_type,
                            'entity_id' => $req->entity_id,
                        ],
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify manager: " . $e->getMessage());
        }
    }
}
