<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Shift;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use App\Models\RestaurantTable;
use App\Models\CustomerSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\GroceryItem;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\MeasurementUnit;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Models\Supplier;
use App\Models\ApprovalRequest;
use App\Services\ShiftService;
use App\Services\SessionService;
use App\Services\OrderService;
use App\Services\InventoryService;
use App\Services\BillingService;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Exception;

class RestaurantErpServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $manager;
    protected $chef;
    protected $waiter;
    protected $table;
    protected $unit;
    protected $supplier;
    protected $groceryItem;
    protected $menuItem;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Tenant Structure
        $this->restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'subscription_plan' => 'pro',
            'features' => [],
            'settings' => [
                'gst_rate' => 5.0,
                'invoice_prefix' => 'TX',
                'service_charge_pct' => 5.0,
                'extra_charge_label' => 'Service Charge',
                'require_waiter_assignment' => true,
            ],
            'user_limits' => 10,
            'table_limits' => 10,
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test Branch',
            'is_active' => true,
        ]);

        // Bind tenant context
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);
        app()->bind('tenant.branch_id', fn() => $this->branch->id);

        // 2. Create Roles
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'chef', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web']);

        // 3. Create Users
        $this->manager = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Manager User',
            'email' => 'test-manager@annsathi.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->manager->assignRole('manager');

        $this->chef = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Chef User',
            'email' => 'test-chef@annsathi.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->chef->assignRole('chef');

        $this->waiter = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Waiter User',
            'email' => 'test-waiter@annsathi.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->waiter->assignRole('waiter');

        $this->actingAs($this->manager);

        // 4. Create Tables
        $this->table = RestaurantTable::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Table T1',
            'capacity' => 4,
            'qr_token' => 'qr-test-t1',
            'status' => 'available',
        ]);

        // 5. Create Measurement Unit & Supplier
        $this->unit = MeasurementUnit::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Gram',
            'short_name' => 'gm',
            'conversion_factor' => 1.0,
        ]);

        $this->supplier = Supplier::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        // 6. Create Grocery Item (Raw Stock)
        $this->groceryItem = GroceryItem::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'measurement_unit_id' => $this->unit->id,
            'supplier_id' => $this->supplier->id,
            'name' => 'Test Ingredient',
            'sku' => 'ING-TEST-01',
            'current_stock' => 0.0,
            'low_stock_threshold' => 100.0,
            'cost_per_unit' => 0.50,
            'avg_cost_per_unit' => 0.50,
        ]);

        // 7. Create Category & Menu Item
        $this->category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test Category',
            'slug' => 'test-category',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->menuItem = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id' => $this->category->id,
            'name' => 'Test Dish',
            'slug' => 'test-dish',
            'base_price' => 100.00,
            'type' => 'veg',
            'item_nature' => 'made_to_order',
            'prep_time_minutes' => 10,
            'is_available' => true,
        ]);

        // 8. Bind Recipe: 1 unit of Test Dish requires 50g of Test Ingredient
        Recipe::create([
            'menu_item_id' => $this->menuItem->id,
            'item_variant_id' => null,
            'grocery_item_id' => $this->groceryItem->id,
            'measurement_unit_id' => $this->unit->id,
            'quantity_required' => 50.0000,
            'is_current' => true,
        ]);
    }

    public function test_complete_restaurant_operations_flow(): void
    {
        // --- 1. Shift Service: Open Shift ---
        $shiftService = app(ShiftService::class);
        $shift = $shiftService->openShift([
            'name' => 'Morning Shift Test',
            'shift_type' => 'morning',
            'started_by' => $this->manager->id,
            'opening_balance' => 1000.00,
        ]);

        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('cash_drawers', [
            'shift_id' => $shift->id,
            'opening_balance' => 1000.00,
            'status' => 'open',
        ]);

        // --- 2. Inventory: Add Stock Batch ---
        // Manually create an inventory batch
        $batch = InventoryBatch::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'grocery_item_id' => $this->groceryItem->id,
            'batch_number' => 'B1',
            'supplier_id' => $this->supplier->id,
            'initial_quantity' => 1000.0000,
            'current_quantity' => 1000.0000,
            'unit_cost' => 0.50,
            'received_date' => now()->toDateString(),
        ]);

        $this->groceryItem->update(['current_stock' => 1000.0000]);

        // --- 3. Session Service: Start Seating Session ---
        $sessionService = app(SessionService::class);
        $session = $sessionService->startSession([
            'session_type' => 'table',
            'sessionable_id' => $this->table->id,
            'customer_name' => 'Alice',
            'pax_count' => 2,
            'shift_id' => $shift->id,
        ]);

        $this->assertDatabaseHas('customer_sessions', [
            'id' => $session->id,
            'status' => 'active',
        ]);

        $this->assertEquals('occupied', $this->table->fresh()->status);

        // --- 4. Order Service: Create Order ---
        $orderService = app(OrderService::class);
        $order = $orderService->createOrder([
            'customer_session_id' => $session->id,
            'service_type' => 'dine_in',
            'assigned_waiter_id' => $this->waiter->id,
            'shift_id' => $shift->id,
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 2, // requires 2 * 50g = 100g ingredient
                ]
            ],
            'created_by' => $this->waiter->id,
        ]);

        // Calculations check:
        // Subtotal = 2 * 100.00 = 200.00
        // Service Charge (extra) = 5% of 200 = 10.00
        // GST (tax) = 5% of 200 = 10.00
        // Total = 220.00
        $this->assertEquals(200.00, $order->subtotal);
        $this->assertEquals(10.00, $order->extra_charges);
        $this->assertEquals(10.00, $order->tax_amount);
        $this->assertEquals(220.00, $order->total_amount);

        // --- 5. Confirm Order & Verify Stock Deduction ---
        $orderService->confirmOrder($order->id);

        $this->assertEquals('confirmed', $order->fresh()->status);

        // Verify stock deducted: 1000g - 100g = 900g
        $this->assertEquals(900.0000, $this->groceryItem->fresh()->current_stock);
        $this->assertEquals(900.0000, $batch->fresh()->current_quantity);

        // --- 6. Billing Service: Pay & Invoice ---
        // Record Payment
        $payment = Payment::create([
            'order_id' => $order->id,
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $shift->id,
            'payment_method' => 'cash',
            'amount' => 220.00,
            'received_by' => $this->manager->id,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Record cash drawer cash movement
        $drawer = CashDrawer::where('shift_id', $shift->id)->where('status', 'open')->first();
        CashMovement::create([
            'cash_drawer_id' => $drawer->id,
            'restaurant_id' => $this->restaurant->id,
            'type' => 'cash_in',
            'amount' => 220.00,
            'reason' => 'Payment for Order',
            'recorded_by' => $this->manager->id,
        ]);

        $orderService->completeOrder($order->id);
        $this->assertEquals('completed', $order->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);

        // Trigger invoice creation
        $billingService = app(BillingService::class);
        $invoice = $billingService->generateInvoice($order, $payment);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'grand_total' => 220.00,
        ]);

        // --- 7. Close Seating Session ---
        $sessionService->closeSession($session->id);
        $this->assertEquals('closed', $session->fresh()->status);
        $this->assertEquals('available', $this->table->fresh()->status);

        // --- 8. Shift Service: Close Shift & Reconcile ---
        $shiftService->closeShift($shift->id, [
            'ended_by' => $this->manager->id,
            'closing_balance' => 1220.00, // 1000.00 opening + 220.00 cash payment
        ]);

        $this->assertEquals('closed', $shift->fresh()->status);

        $closedDrawer = CashDrawer::where('shift_id', $shift->id)->first();
        $this->assertEquals('closed', $closedDrawer->status);
        $this->assertEquals(1220.00, $closedDrawer->closing_balance);
        $this->assertEquals(1220.00, $closedDrawer->expected_closing_balance);
        $this->assertEquals(0.00, $closedDrawer->variance);
    }

    public function test_refund_workflow_with_approvals(): void
    {
        // Create paid payment
        $payment = Payment::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'amount' => 100.00,
            'payment_method' => 'cash',
            'received_by' => $this->manager->id,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $invoice = Invoice::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'payment_id' => $payment->id,
            'invoice_number' => 'TX-INV-001',
            'invoice_prefix' => 'TX',
            'invoice_sequence' => 1,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 100.00,
            'grand_total' => 100.00,
            'items_snapshot' => [],
        ]);

        // 1. Attempt refund without approval request - Should fail
        $billingService = app(BillingService::class);
        $this->expectException(Exception::class);
        $billingService->refundPayment($payment, 100.00, 'Customer dissatisfied', 'cash', null);

        // 2. Create approval request
        $approvalService = app(ApprovalService::class);
        $req = $approvalService->createRequest([
            'entity_type' => Payment::class,
            'entity_id' => $payment->id,
            'action' => 'refund',
            'reason' => 'Defective dish served',
            'requested_by' => $this->waiter->id,
        ]);

        // 3. Attempt refund before approval - Should fail
        try {
            $billingService->refundPayment($payment, 100.00, 'Customer dissatisfied', 'cash', $req->id);
            $this->fail("Refund processed without manager approval confirmation.");
        } catch (Exception $e) {
            $this->assertStringContainsString('Only approved requests can be processed', $e->getMessage());
        }

        // 4. Approve request
        $approvalService->approveRequest($req->id, $this->manager->id);

        // 5. Run refund - Should succeed
        $refund = $billingService->refundPayment($payment, 100.00, 'Customer dissatisfied', 'cash', $req->id);

        $this->assertDatabaseHas('refunds', [
            'id' => $refund->id,
            'amount' => 100.00,
        ]);

        $this->assertEquals('refunded', $payment->fresh()->status);
        $this->assertNotNull($invoice->fresh()->voided_by_credit_note_id);
    }
}
