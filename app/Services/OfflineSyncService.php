<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OfflineAction;
use App\Models\SyncQueue;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use App\Domains\Reservations\Services\ReservationService;
use Illuminate\Support\Facades\DB;
use Exception;

class OfflineSyncService
{
    /**
     * Push a new offline action to the local history and the sync queue.
     */
    public function pushOfflineAction(array $data): OfflineAction
    {
        return DB::transaction(function () use ($data) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');

            // 1. Create Offline Action record (local log/history)
            $action = OfflineAction::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'device_identifier' => $data['device_identifier'] ?? 'offline-client',
                'action_type' => $data['action_type'],
                'payload' => $data['payload'],
                'status' => 'pending',
            ]);

            // 2. Create Sync Queue item
            SyncQueue::create([
                'branch_id' => $branchId,
                'action' => $data['action_type'],
                'payload' => $data['payload'],
                'status' => 'pending',
                'attempts' => 0,
            ]);

            return $action;
        });
    }

    /**
     * Process all pending items in the sync queue.
     */
    public function processSyncQueue(): array
    {
        $pendingItems = SyncQueue::whereIn('status', ['pending', 'failed'])
            ->where('attempts', '<', 3)
            ->orderBy('id', 'asc')
            ->get();

        $results = [
            'total' => $pendingItems->count(),
            'synced' => 0,
            'failed' => 0,
        ];

        foreach ($pendingItems as $item) {
            $item->attempts += 1;
            $item->save();

            try {
                DB::transaction(function () use ($item) {
                    $this->executeSyncAction($item->action, $item->payload, $item->branch_id);
                    $item->status = 'synced';
                    $item->save();

                    // Find and update matching OfflineAction log if exists
                    OfflineAction::where('branch_id', $item->branch_id)
                        ->where('action_type', $item->action)
                        ->where('status', 'pending')
                        ->first()
                        ?->update([
                            'status' => 'completed',
                            'synced_at' => now(),
                        ]);
                });
                $results['synced']++;
            } catch (Exception $e) {
                $item->status = 'failed';
                $item->save();

                OfflineAction::where('branch_id', $item->branch_id)
                    ->where('action_type', $item->action)
                    ->where('status', 'pending')
                    ->first()
                    ?->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);

                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Execute specific sync action based on action type.
     */
    protected function executeSyncAction(string $action, array $payload, ?int $branchId): void
    {
        // Bind branch/restaurant tenant context if available
        if ($branchId) {
            $branch = \App\Models\Branch::find($branchId);
            if ($branch) {
                app()->bind('tenant.restaurant_id', fn() => $branch->restaurant_id);
                app()->bind('tenant.branch_id', fn() => $branch->id);
            }
        }

        switch ($action) {
            case 'create_order':
                app(OrderService::class)->createOrder($payload);
                break;

            case 'create_payment':
                $this->processOfflinePayment($payload);
                break;

            case 'create_reservation':
                app(ReservationService::class)->createReservation($payload);
                break;

            case 'sync_sale':
                $this->processOfflineSale($payload);
                break;

            default:
                throw new Exception("Unknown offline sync action: {$action}");
        }
    }

    /**
     * Handle creating payment + invoice from offline sync.
     */
    protected function processOfflinePayment(array $payload): void
    {
        $order = Order::findOrFail($payload['order_id']);

        $payment = Payment::create([
            'order_id' => $order->id,
            'restaurant_id' => $order->restaurant_id,
            'branch_id' => $order->branch_id,
            'shift_id' => $payload['shift_id'] ?? $order->shift_id,
            'payment_method' => $payload['payment_method'],
            'amount' => $payload['amount'] ?? $order->total_amount,
            'reference_note' => $payload['reference_note'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'received_by' => $payload['received_by'] ?? auth()->id(),
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        if ($payload['payment_method'] === 'cash') {
            $drawer = CashDrawer::where('shift_id', $payment->shift_id)
                ->where('status', 'open')
                ->first();

            if ($drawer) {
                CashMovement::create([
                    'cash_drawer_id' => $drawer->id,
                    'restaurant_id' => $order->restaurant_id,
                    'type' => 'cash_in',
                    'amount' => $payment->amount,
                    'reason' => 'Offline Synced Payment for Order #' . $order->id,
                    'recorded_by' => $payment->received_by,
                    'created_at' => now(),
                ]);
            }
        }

        app(OrderService::class)->completeOrder($order->id);
        app(BillingService::class)->generateInvoice($order, $payment);

        if (($payload['release_table'] ?? false) && $order->customer_session_id) {
            app(SessionService::class)->closeSession($order->customer_session_id, $payment->received_by);
        }
    }

    /**
     * Handle composite sale (order + payment) in single sync payload.
     */
    protected function processOfflineSale(array $payload): void
    {
        // 1. Create the Order
        $order = app(OrderService::class)->createOrder($payload['order']);

        // 2. Create the Payment
        $paymentPayload = $payload['payment'];
        $paymentPayload['order_id'] = $order->id;

        $this->processOfflinePayment($paymentPayload);
    }

    /**
     * Reset attempts for failed actions to try again.
     */
    public function reprocessFailed(): void
    {
        SyncQueue::where('status', 'failed')->update([
            'status' => 'pending',
            'attempts' => 0,
        ]);
    }
}
