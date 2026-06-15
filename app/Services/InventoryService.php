<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\GroceryItem;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\WasteRecord;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Deduct stock for an order's items based on recipes using FIFO batch allocation.
     */
    public function deductStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $item) {
                // Skip if item is already cancelled
                if ($item->status === 'cancelled') {
                    continue;
                }

                $this->deductStockForItem($item);
            }
        });
    }

    /**
     * Deduct stock for a single order item.
     */
    public function deductStockForItem(OrderItem $item): void
    {
        DB::transaction(function () use ($item) {
            // Find current recipe for the menu item
            $recipes = Recipe::where('menu_item_id', $item->menu_item_id)
                ->when($item->selected_variant_id, function ($q) use ($item) {
                    return $q->where('item_variant_id', $item->selected_variant_id);
                })
                ->where('is_current', true)
                ->get();

            foreach ($recipes as $recipe) {
                $groceryItem = GroceryItem::lockForUpdate()->findOrFail($recipe->grocery_item_id);
                $qtyNeeded = $recipe->quantity_required * $item->quantity;

                // Ensure we have enough total stock (allow some flexibility if config allows, but throw error here)
                if ($groceryItem->current_stock < $qtyNeeded) {
                    throw new Exception("Insufficient stock for raw material: {$groceryItem->name}. Required: {$qtyNeeded}, Available: {$groceryItem->current_stock}");
                }

                $remainingQty = $qtyNeeded;

                // FIFO Batch allocation
                $batches = InventoryBatch::where('grocery_item_id', $groceryItem->id)
                    ->where('current_quantity', '>', 0)
                    ->orderBy('received_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    if ($remainingQty <= 0) {
                        break;
                    }

                    $deductQty = min($batch->current_quantity, $remainingQty);
                    
                    // Update batch
                    $batch->update([
                        'current_quantity' => $batch->current_quantity - $deductQty
                    ]);

                    // Write transaction linked to this batch
                    InventoryTransaction::create([
                        'restaurant_id' => $item->order->restaurant_id,
                        'branch_id' => $item->order->branch_id,
                        'grocery_item_id' => $groceryItem->id,
                        'inventory_batch_id' => $batch->id,
                        'type' => 'order_fulfillment',
                        'quantity' => -$deductQty,
                        'balance_after' => $groceryItem->current_stock - $deductQty,
                        'unit_cost' => $batch->unit_cost,
                        'total_cost' => $deductQty * $batch->unit_cost,
                        'reference_type' => OrderItem::class,
                        'reference_id' => $item->id,
                        'performed_by' => auth()->id(),
                    ]);

                    $remainingQty -= $deductQty;
                }

                // If some quantity remains (e.g. no active batches but main stock was somehow positive), deduct it
                if ($remainingQty > 0) {
                    InventoryTransaction::create([
                        'restaurant_id' => $item->order->restaurant_id,
                        'branch_id' => $item->order->branch_id,
                        'grocery_item_id' => $groceryItem->id,
                        'inventory_batch_id' => null,
                        'type' => 'order_fulfillment',
                        'quantity' => -$remainingQty,
                        'balance_after' => $groceryItem->current_stock - $remainingQty,
                        'unit_cost' => $groceryItem->cost_per_unit,
                        'total_cost' => $remainingQty * ($groceryItem->cost_per_unit ?? 0),
                        'reference_type' => OrderItem::class,
                        'reference_id' => $item->id,
                        'performed_by' => auth()->id(),
                    ]);
                }

                // Update main item stock
                $groceryItem->update([
                    'current_stock' => max(0, $groceryItem->current_stock - $qtyNeeded)
                ]);
            }
        });
    }

    /**
     * Restore stock when an order is cancelled.
     */
    public function restoreStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Retrieve all order_fulfillment transactions associated with this order's items
            $itemIds = $order->orderItems->pluck('id');
            $transactions = InventoryTransaction::where('reference_type', OrderItem::class)
                ->whereIn('reference_id', $itemIds)
                ->where('type', 'order_fulfillment')
                ->get();

            foreach ($transactions as $tx) {
                $groceryItem = GroceryItem::lockForUpdate()->findOrFail($tx->grocery_item_id);
                $restoreQty = abs($tx->quantity);

                // Add stock back to batch if it was linked
                if ($tx->inventory_batch_id) {
                    $batch = InventoryBatch::lockForUpdate()->find($tx->inventory_batch_id);
                    if ($batch) {
                        $batch->update([
                            'current_quantity' => $batch->current_quantity + $restoreQty
                        ]);
                    }
                }

                // Update main item stock
                $newStock = $groceryItem->current_stock + $restoreQty;
                $groceryItem->update([
                    'current_stock' => $newStock
                ]);

                // Create reverse transaction
                InventoryTransaction::create([
                    'restaurant_id' => $order->restaurant_id,
                    'branch_id' => $order->branch_id,
                    'grocery_item_id' => $groceryItem->id,
                    'inventory_batch_id' => $tx->inventory_batch_id,
                    'type' => 'order_cancellation',
                    'quantity' => $restoreQty,
                    'balance_after' => $newStock,
                    'unit_cost' => $tx->unit_cost,
                    'total_cost' => $tx->total_cost,
                    'reference_type' => OrderItem::class,
                    'reference_id' => $tx->reference_id,
                    'performed_by' => auth()->id(),
                ]);
            }
        });
    }

    /**
     * Process stock replenishment once a Goods Receipt Note (GRN) is received/approved.
     */
    public function replenishStockFromGrn(GoodsReceipt $grn): void
    {
        DB::transaction(function () use ($grn) {
            $grn->load('items');

            foreach ($grn->items as $grnItem) {
                $groceryItem = GroceryItem::lockForUpdate()->findOrFail($grnItem->grocery_item_id);
                $qty = $grnItem->quantity_received;
                $cost = $grnItem->unit_cost;

                // Create a new batch
                $batch = InventoryBatch::create([
                    'restaurant_id' => $grn->restaurant_id,
                    'branch_id' => $grn->branch_id,
                    'grocery_item_id' => $groceryItem->id,
                    'batch_number' => $grnItem->batch_number ?: 'BATCH-' . $grn->id . '-' . $groceryItem->sku,
                    'supplier_id' => $grn->purchaseOrder->supplier_id,
                    'initial_quantity' => $qty,
                    'current_quantity' => $qty,
                    'unit_cost' => $cost,
                    'received_date' => $grn->receipt_date ?: now()->toDateString(),
                    'expiry_date' => $grnItem->expiry_date,
                ]);

                // Update grocery item average cost and current stock
                $oldStock = $groceryItem->current_stock;
                $newStock = $oldStock + $qty;

                // Weighted Average Cost calculation
                $oldTotalValue = $oldStock * ($groceryItem->avg_cost_per_unit ?: $groceryItem->cost_per_unit ?: 0);
                $newTotalValue = $oldTotalValue + ($qty * $cost);
                $newAvgCost = $newStock > 0 ? $newTotalValue / $newStock : $cost;

                $groceryItem->update([
                    'current_stock' => $newStock,
                    'cost_per_unit' => $cost,
                    'avg_cost_per_unit' => $newAvgCost,
                ]);

                // Write transaction
                InventoryTransaction::create([
                    'restaurant_id' => $grn->restaurant_id,
                    'branch_id' => $grn->branch_id,
                    'grocery_item_id' => $groceryItem->id,
                    'inventory_batch_id' => $batch->id,
                    'type' => 'purchase_receipt',
                    'quantity' => $qty,
                    'balance_after' => $newStock,
                    'unit_cost' => $cost,
                    'total_cost' => $qty * $cost,
                    'reference_type' => GoodsReceiptItem::class,
                    'reference_id' => $grnItem->id,
                    'performed_by' => $grn->received_by,
                ]);
            }
        });
    }

    /**
     * Record a kitchen waste incident.
     */
    public function recordWaste(array $data): WasteRecord
    {
        return DB::transaction(function () use ($data) {
            $groceryItem = GroceryItem::lockForUpdate()->findOrFail($data['grocery_item_id']);
            $qty = $data['quantity'];
            $cost = $groceryItem->cost_per_unit ?? 0;
            $totalCost = $qty * $cost;

            $waste = WasteRecord::create([
                'restaurant_id' => app('tenant.restaurant_id'),
                'branch_id' => app('tenant.branch_id'),
                'grocery_item_id' => $groceryItem->id,
                'measurement_unit_id' => $groceryItem->measurement_unit_id,
                'quantity' => $qty,
                'unit_cost' => $cost,
                'total_cost' => $totalCost,
                'reason' => $data['reason'], // expired, spoilage, kitchen_mistake, etc.
                'reason_notes' => $data['reason_notes'] ?? null,
                'recorded_by' => auth()->id() ?: $data['recorded_by'] ?? null,
                'shift_id' => $data['shift_id'] ?? null,
            ]);

            // Deduct stock
            $oldStock = $groceryItem->current_stock;
            $newStock = max(0, $oldStock - $qty);
            $groceryItem->update([
                'current_stock' => $newStock
            ]);

            // Write transaction (deduct from batches using FIFO if possible)
            $remainingQty = $qty;
            $batches = InventoryBatch::where('grocery_item_id', $groceryItem->id)
                ->where('current_quantity', '>', 0)
                ->orderBy('received_date', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remainingQty <= 0) break;
                
                $deductQty = min($batch->current_quantity, $remainingQty);
                $batch->update([
                    'current_quantity' => $batch->current_quantity - $deductQty
                ]);

                InventoryTransaction::create([
                    'restaurant_id' => $waste->restaurant_id,
                    'branch_id' => $waste->branch_id,
                    'grocery_item_id' => $groceryItem->id,
                    'inventory_batch_id' => $batch->id,
                    'type' => 'waste',
                    'quantity' => -$deductQty,
                    'balance_after' => $groceryItem->current_stock - ($qty - $remainingQty) - $deductQty,
                    'unit_cost' => $batch->unit_cost,
                    'total_cost' => $deductQty * $batch->unit_cost,
                    'reference_type' => WasteRecord::class,
                    'reference_id' => $waste->id,
                    'performed_by' => $waste->recorded_by,
                ]);

                $remainingQty -= $deductQty;
            }

            if ($remainingQty > 0) {
                InventoryTransaction::create([
                    'restaurant_id' => $waste->restaurant_id,
                    'branch_id' => $waste->branch_id,
                    'grocery_item_id' => $groceryItem->id,
                    'inventory_batch_id' => null,
                    'type' => 'waste',
                    'quantity' => -$remainingQty,
                    'balance_after' => $newStock,
                    'unit_cost' => $cost,
                    'total_cost' => $remainingQty * $cost,
                    'reference_type' => WasteRecord::class,
                    'reference_id' => $waste->id,
                    'performed_by' => $waste->recorded_by,
                ]);
            }

            return $waste;
        });
    }
}
