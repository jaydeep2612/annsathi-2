<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Domains\Procurement\Exceptions\ProcurementException;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderService
{
    protected InventoryService $inventoryService;
    protected SupplierLedgerService $supplierLedgerService;

    public function __construct(InventoryService $inventoryService, SupplierLedgerService $supplierLedgerService)
    {
        $this->inventoryService = $inventoryService;
        $this->supplierLedgerService = $supplierLedgerService;
    }

    /**
     * Process receiving goods against a Purchase Order.
     * Creates a GoodsReceipt and updates PO item received quantities and overall status.
     * Restocks the items in the warehouse/branch and credits the supplier ledger.
     */
    public function receiveGoods(int|PurchaseOrder $po, array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($po, $data) {
            $poModel = $po instanceof PurchaseOrder 
                ? $po 
                : PurchaseOrder::lockForUpdate()->findOrFail($po);

            // Validate status: can only receive on 'sent' or 'partial' POs
            if (!in_array($poModel->status, ['sent', 'partial'])) {
                throw ProcurementException::poNotOpen($poModel->po_number, $poModel->status);
            }

            // Create Goods Receipt Note
            $goodsReceipt = GoodsReceipt::create([
                'purchase_order_id' => $poModel->id,
                'restaurant_id' => $poModel->restaurant_id,
                'branch_id' => $poModel->branch_id,
                'received_by' => Auth::id() ?: $data['received_by'] ?? $poModel->ordered_by,
                'receipt_date' => $data['receipt_date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            $grnTotalCost = 0.00;
            $grnItemsData = [];

            foreach ($data['items'] as $itemData) {
                $poItem = PurchaseOrderItem::findOrFail($itemData['purchase_order_item_id']);
                
                $qtyReceived = (float) $itemData['quantity_received'];
                $unitCost = (float) ($itemData['unit_cost'] ?? $poItem->unit_price);
                $totalCost = $qtyReceived * $unitCost;

                // Update PO Item received quantity
                $poItem->update([
                    'received_quantity' => $poItem->received_quantity + $qtyReceived
                ]);

                // Create Goods Receipt Item
                $grnItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'purchase_order_item_id' => $poItem->id,
                    'grocery_item_id' => $poItem->grocery_item_id,
                    'quantity_received' => $qtyReceived,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'quality_status' => $itemData['quality_status'] ?? 'accepted',
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $grnTotalCost += $totalCost;
                $grnItemsData[] = $grnItem;
            }

            // Load items relationship for inventory replenishment
            $goodsReceipt->setRelation('items', collect($grnItemsData));

            // Replenish stock via InventoryService (which creates FIFO batches and updates averages)
            $this->inventoryService->replenishStockFromGrn($goodsReceipt);

            // Record Credit to Supplier Ledger
            $this->supplierLedgerService->recordCredit(
                supplier: $poModel->supplier_id,
                amount: $grnTotalCost,
                referenceType: GoodsReceipt::class,
                referenceId: $goodsReceipt->id,
                notes: "Goods Receipt Note #{$goodsReceipt->id} received for PO #{$poModel->po_number}",
                branchId: $poModel->branch_id
            );

            // Update Purchase Order Status
            $poModel->load('items');
            $allReceived = true;
            $anyReceived = false;

            foreach ($poModel->items as $item) {
                if ($item->received_quantity < $item->ordered_quantity) {
                    $allReceived = false;
                }
                if ($item->received_quantity > 0) {
                    $anyReceived = true;
                }
            }

            $newStatus = $allReceived ? 'received' : ($anyReceived ? 'partial' : 'sent');
            $poModel->update([
                'status' => $newStatus
            ]);

            app(\App\Domains\Accounting\Services\AccountingService::class)->postGoodsReceipt($goodsReceipt);

            return $goodsReceipt;
        });
    }
}
