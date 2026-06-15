<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Events\CreditNoteIssued;
use App\Events\ApprovalApproved;
use App\Events\ShiftClosed;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class CreateActivityLogListener implements ShouldQueue
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
            $restaurantId = null;
            $userId = auth()->id();
            $eventName = '';
            $description = '';
            $oldValues = null;
            $newValues = null;

            if ($event instanceof OrderCancelled) {
                $restaurantId = $event->order->restaurant_id;
                $eventName = 'order.cancelled';
                $description = "Order ID {$event->order->id} was cancelled. Reason: {$event->reason}";
                $newValues = [
                    'order_id' => $event->order->id,
                    'status' => 'cancelled',
                    'reason' => $event->reason,
                ];
            } elseif ($event instanceof CreditNoteIssued) {
                $restaurantId = $event->creditNote->restaurant_id;
                $eventName = 'credit_note.issued';
                $description = "Credit Note {$event->creditNote->credit_note_number} was issued for Invoice ID {$event->creditNote->invoice_id}. Amount: {$event->creditNote->amount}";
                $newValues = [
                    'credit_note_id' => $event->creditNote->id,
                    'invoice_id' => $event->creditNote->invoice_id,
                    'amount' => $event->creditNote->amount,
                ];
            } elseif ($event instanceof ApprovalApproved) {
                $restaurantId = $event->approvalRequest->restaurant_id;
                $eventName = 'override.approved';
                $description = "Override approved for action '{$event->approvalRequest->action}' on entity '{$event->approvalRequest->entity_type}' ID {$event->approvalRequest->entity_id} by User ID {$event->approvalRequest->approved_by}";
                $newValues = [
                    'approval_request_id' => $event->approvalRequest->id,
                    'action' => $event->approvalRequest->action,
                    'approved_by' => $event->approvalRequest->approved_by,
                ];
            } elseif ($event instanceof ShiftClosed) {
                $restaurantId = $event->shift->restaurant_id;
                $eventName = 'shift.closed';
                $description = "Shift ID {$event->shift->id} ('{$event->shift->name}') was closed by User ID {$event->shift->ended_by}.";
                $newValues = [
                    'shift_id' => $event->shift->id,
                    'ended_by' => $event->shift->ended_by,
                    'end_time' => $event->shift->end_time,
                ];
            }

            if ($eventName) {
                AuditLog::create([
                    'restaurant_id' => $restaurantId,
                    'user_id' => $userId,
                    'event' => $eventName,
                    'description' => $description,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'created_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to write audit log: " . $e->getMessage());
        }
    }
}
