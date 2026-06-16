<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\GroceryItem;
use App\Models\MeasurementUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Domains\Procurement\Services\SupplierLedgerService;
use App\Domains\Procurement\Services\PurchaseOrderService;
use App\Domains\Procurement\Exceptions\ProcurementException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class ProcurementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $supplier;
    protected $unit;
    protected $groceryItem;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Pizza Palace',
            'slug' => 'pizza-palace',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Main POS',
            'is_active' => true,
        ]);

        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);
        app()->bind('tenant.branch_id', fn() => $this->branch->id);

        $this->unit = MeasurementUnit::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Litre',
            'short_name' => 'ltr',
            'conversion_factor' => 1.0,
        ]);

        $this->supplier = Supplier::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Milk Supplier Ltd',
            'is_active' => true,
        ]);

        $this->groceryItem = GroceryItem::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'measurement_unit_id' => $this->unit->id,
            'supplier_id' => $this->supplier->id,
            'name' => 'Whole Milk',
            'sku' => 'MILK-01',
            'current_stock' => 0.0,
            'cost_per_unit' => 2.50,
        ]);

        $this->user = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Purchasing Manager',
            'email' => 'purchase@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);
    }

    /**
     * Test recording debit and credit entries in supplier ledger.
     */
    public function test_record_credit_and_debit_in_supplier_ledger(): void
    {
        $ledgerService = app(SupplierLedgerService::class);

        // 1. Credit 500.00 (received goods invoice)
        $creditEntry = $ledgerService->recordCredit(
            supplier: $this->supplier,
            amount: 500.00,
            referenceType: 'GoodsReceipt',
            referenceId: 123,
            notes: 'Received initial milk batch'
        );

        $this->assertEquals('credit', $creditEntry->type);
        $this->assertEquals(500.00, $creditEntry->amount);
        $this->assertEquals(500.00, $creditEntry->balance_after);
        $this->assertEquals(500.00, $this->supplier->fresh()->balance);

        // 2. Debit 200.00 (made a payment to supplier)
        $debitEntry = $ledgerService->recordDebit(
            supplier: $this->supplier,
            amount: 200.00,
            referenceType: 'Payment',
            referenceId: 456,
            notes: 'Paid part of invoice'
        );

        $this->assertEquals('debit', $debitEntry->type);
        $this->assertEquals(200.00, $debitEntry->amount);
        $this->assertEquals(300.00, $debitEntry->balance_after);
        $this->assertEquals(300.00, $this->supplier->fresh()->balance);
    }

    /**
     * Test validation throws exception for invalid amount.
     */
    public function test_ledger_fails_for_zero_or_negative_amount(): void
    {
        $ledgerService = app(SupplierLedgerService::class);

        $this->expectException(ProcurementException::class);
        $this->expectExceptionMessage('Amount must be greater than zero.');
        $ledgerService->recordCredit($this->supplier, -10.00);
    }

    /**
     * Test full receive goods flow (PO -> GRN -> Stock -> Ledger).
     */
    public function test_receive_goods_replenishes_stock_and_updates_po_status_and_ledger(): void
    {
        $poService = app(PurchaseOrderService::class);

        // 1. Create Purchase Order
        $po = PurchaseOrder::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-2026-001',
            'status' => 'sent',
            'ordered_by' => $this->user->id,
            'total_amount' => 50.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'grocery_item_id' => $this->groceryItem->id,
            'measurement_unit_id' => $this->unit->id,
            'ordered_quantity' => 10.0000,
            'received_quantity' => 0.0000,
            'unit_price' => 5.00,
            'total_price' => 50.00,
        ]);

        // 2. Receive Goods fully
        $grnData = [
            'receipt_date' => now()->toDateString(),
            'notes' => 'Fully received milk order',
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'quantity_received' => 10.0000,
                    'unit_cost' => 5.00,
                    'batch_number' => 'MILK-BATCH-001',
                    'expiry_date' => now()->addDays(7)->toDateString(),
                    'quality_status' => 'accepted',
                ]
            ]
        ];

        $grn = $poService->receiveGoods($po, $grnData);

        // 3. Assertions
        $this->assertDatabaseHas('goods_receipts', [
            'id' => $grn->id,
            'purchase_order_id' => $po->id,
        ]);

        $this->assertDatabaseHas('goods_receipt_items', [
            'goods_receipt_id' => $grn->id,
            'purchase_order_item_id' => $poItem->id,
            'quantity_received' => 10.0000,
            'unit_cost' => 5.00,
        ]);

        // PO status should be received
        $this->assertEquals('received', $po->fresh()->status);
        $this->assertEquals(10.0000, $poItem->fresh()->received_quantity);

        // Stock should be replenished in grocery_items
        $this->assertEquals(10.0000, $this->groceryItem->fresh()->current_stock);
        // Average cost and last cost should be updated
        $this->assertEquals(5.00, $this->groceryItem->fresh()->cost_per_unit);
        $this->assertEquals(5.00, $this->groceryItem->fresh()->avg_cost_per_unit);

        // FIFO batch should be created
        $this->assertDatabaseHas('inventory_batches', [
            'grocery_item_id' => $this->groceryItem->id,
            'current_quantity' => 10.0000,
            'unit_cost' => 5.00,
        ]);

        // Inventory Transaction should be written
        $this->assertDatabaseHas('inventory_transactions', [
            'grocery_item_id' => $this->groceryItem->id,
            'type' => 'purchase_receipt',
            'quantity' => 10.0000,
            'reference_type' => GoodsReceiptItem::class,
        ]);

        // Supplier Ledger credit entry should be posted
        $this->assertDatabaseHas('supplier_ledgers', [
            'supplier_id' => $this->supplier->id,
            'type' => 'credit',
            'amount' => 50.00,
            'balance_after' => 50.00,
            'reference_type' => GoodsReceipt::class,
            'reference_id' => $grn->id,
        ]);

        $this->assertEquals(50.00, $this->supplier->fresh()->balance);
    }

    /**
     * Test partial receive goods flow.
     */
    public function test_partial_receive_goods_transitions_status_to_partial(): void
    {
        $poService = app(PurchaseOrderService::class);

        $po = PurchaseOrder::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-2026-002',
            'status' => 'sent',
            'ordered_by' => $this->user->id,
            'total_amount' => 50.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'grocery_item_id' => $this->groceryItem->id,
            'measurement_unit_id' => $this->unit->id,
            'ordered_quantity' => 10.0000,
            'received_quantity' => 0.0000,
            'unit_price' => 5.00,
            'total_price' => 50.00,
        ]);

        // Receive only 4 units
        $grnData = [
            'receipt_date' => now()->toDateString(),
            'notes' => 'Partially received milk order due to delivery shortage',
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'quantity_received' => 4.0000,
                    'unit_cost' => 5.00,
                ]
            ]
        ];

        $grn = $poService->receiveGoods($po, $grnData);

        // PO status should be partial
        $this->assertEquals('partial', $po->fresh()->status);
        $this->assertEquals(4.0000, $poItem->fresh()->received_quantity);

        // Stock replenished by 4
        $this->assertEquals(4.0000, $this->groceryItem->fresh()->current_stock);

        // Ledger credited by 20.00
        $this->assertEquals(20.00, $this->supplier->fresh()->balance);
    }

    /**
     * Test receiving goods fails on draft/cancelled PO.
     */
    public function test_receive_goods_fails_if_po_not_sent_or_partial(): void
    {
        $poService = app(PurchaseOrderService::class);

        $po = PurchaseOrder::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-2026-003',
            'status' => 'draft', // Draft PO
            'ordered_by' => $this->user->id,
            'total_amount' => 50.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'grocery_item_id' => $this->groceryItem->id,
            'measurement_unit_id' => $this->unit->id,
            'ordered_quantity' => 10.0000,
            'received_quantity' => 0.0000,
            'unit_price' => 5.00,
            'total_price' => 50.00,
        ]);

        $grnData = [
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'quantity_received' => 10.0000,
                ]
            ]
        ];

        $this->expectException(ProcurementException::class);
        $this->expectExceptionMessage('is not in a status that can receive goods');
        $poService->receiveGoods($po, $grnData);
    }
}
