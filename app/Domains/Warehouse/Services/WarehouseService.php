<?php

declare(strict_types=1);

namespace App\Domains\Warehouse\Services;

use App\Domains\Warehouse\Models\Warehouse;
use App\Domains\Warehouse\Models\WarehouseStock;
use App\Domains\Warehouse\Models\WarehouseMovement;
use App\Models\GroceryItem;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class WarehouseService
{
    /**
     * Add stock to a central warehouse.
     */
    public function addStockToWarehouse(int $warehouseId, int $groceryItemId, float $quantity, float $unitCost): void
    {
        DB::transaction(function () use ($warehouseId, $groceryItemId, $quantity, $unitCost) {
            $warehouse = Warehouse::findOrFail($warehouseId);
            $item = GroceryItem::findOrFail($groceryItemId);

            // 1. Update or create warehouse stock
            $stock = WarehouseStock::firstOrCreate([
                'warehouse_id' => $warehouseId,
                'grocery_item_id' => $groceryItemId,
            ]);

            $stock->increment('quantity', $quantity);

            // 2. Log warehouse movement
            WarehouseMovement::create([
                'restaurant_id' => $warehouse->restaurant_id,
                'from_warehouse_id' => $warehouseId,
                'to_branch_id' => null,
                'grocery_item_id' => $groceryItemId,
                'quantity' => $quantity,
                'transfer_type' => 'receipt',
                'recorded_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Dispatch stock from central warehouse to a branch.
     */
    public function dispatchStockToBranch(int $warehouseId, int $toBranchId, int $groceryItemId, float $quantity): void
    {
        DB::transaction(function () use ($warehouseId, $toBranchId, $groceryItemId, $quantity) {
            $warehouse = Warehouse::findOrFail($warehouseId);
            $sourceItem = GroceryItem::findOrFail($groceryItemId);

            // 1. Verify warehouse has enough stock
            $stock = WarehouseStock::where('warehouse_id', $warehouseId)
                ->where('grocery_item_id', $groceryItemId)
                ->first();

            if (! $stock || $stock->quantity < $quantity) {
                $available = $stock ? $stock->quantity : 0;
                throw new Exception("Insufficient stock in warehouse '{$warehouse->name}' for item '{$sourceItem->name}'. Requested: {$quantity}, Available: {$available}");
            }

            // 2. Deduct from warehouse
            $stock->decrement('quantity', $quantity);

            // 3. Log warehouse movement
            WarehouseMovement::create([
                'restaurant_id' => $warehouse->restaurant_id,
                'from_warehouse_id' => $warehouseId,
                'to_branch_id' => $toBranchId,
                'grocery_item_id' => $groceryItemId,
                'quantity' => -$quantity,
                'transfer_type' => 'dispatch',
                'recorded_by' => auth()->id(),
                'created_at' => now(),
            ]);

            // 4. Find or create matching grocery item on destination branch
            $branchItem = GroceryItem::where('branch_id', $toBranchId)
                ->where(function ($query) use ($sourceItem) {
                    if ($sourceItem->sku) {
                        $query->where('sku', $sourceItem->sku);
                    } else {
                        $query->where('name', $sourceItem->name);
                    }
                })
                ->first();

            if (! $branchItem) {
                // Duplicate catalog details to target branch
                $branchItem = GroceryItem::create([
                    'restaurant_id' => $warehouse->restaurant_id,
                    'branch_id' => $toBranchId,
                    'measurement_unit_id' => $sourceItem->measurement_unit_id,
                    'supplier_id' => $sourceItem->supplier_id,
                    'name' => $sourceItem->name,
                    'sku' => $sourceItem->sku,
                    'current_stock' => 0.0000,
                    'low_stock_threshold' => $sourceItem->low_stock_threshold,
                    'reorder_quantity' => $sourceItem->reorder_quantity,
                    'cost_per_unit' => $sourceItem->cost_per_unit,
                    'avg_cost_per_unit' => $sourceItem->cost_per_unit,
                ]);
            }

            // 5. Add to branch stock
            $oldStock = $branchItem->current_stock;
            $newStock = $oldStock + $quantity;
            $unitCost = $sourceItem->cost_per_unit ?? 0.00;

            $branchItem->update([
                'current_stock' => $newStock,
            ]);

            // 6. Create new inventory batch for branch FIFO tracking
            $batch = InventoryBatch::create([
                'restaurant_id' => $warehouse->restaurant_id,
                'branch_id' => $toBranchId,
                'grocery_item_id' => $branchItem->id,
                'batch_number' => 'WH-TRANS-' . $warehouseId . '-' . time(),
                'supplier_id' => $branchItem->supplier_id,
                'initial_quantity' => $quantity,
                'current_quantity' => $quantity,
                'unit_cost' => $unitCost,
                'received_date' => now()->toDateString(),
            ]);

            // 7. Write branch inventory transaction
            InventoryTransaction::create([
                'restaurant_id' => $warehouse->restaurant_id,
                'branch_id' => $toBranchId,
                'grocery_item_id' => $branchItem->id,
                'inventory_batch_id' => $batch->id,
                'type' => 'transfer',
                'quantity' => $quantity,
                'balance_after' => $newStock,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'reference_type' => WarehouseMovement::class,
                'reference_id' => $batch->id,
                'performed_by' => auth()->id(),
            ]);
        });
    }
}
