<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Order;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\MeasurementUnit;
use App\Models\GroceryItem;
use App\Domains\Accounting\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class AccountingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $user;
    protected $supplier;
    protected $unit;
    protected $groceryItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Fabulous Bistro',
            'slug' => 'fabulous-bistro',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'HQ Branch',
            'is_active' => true,
        ]);

        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);
        app()->bind('tenant.branch_id', fn() => $this->branch->id);

        $this->user = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Accounting Clerk',
            'email' => 'clerk@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->supplier = Supplier::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Mega Food Supplier',
            'phone' => '1234567890',
            'is_active' => true,
        ]);

        $this->unit = MeasurementUnit::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Kilo',
            'short_name' => 'kg',
            'conversion_factor' => 1.0,
        ]);

        $this->groceryItem = GroceryItem::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'measurement_unit_id' => $this->unit->id,
            'supplier_id' => $this->supplier->id,
            'name' => 'Tomato',
            'sku' => 'TOM-01',
            'current_stock' => 0.0,
            'cost_per_unit' => 10.00,
        ]);

        $this->actingAs($this->user);
    }

    /**
     * Test standard Chart of Accounts seeding.
     */
    public function test_ensure_default_accounts_are_created(): void
    {
        $service = app(AccountingService::class);
        $service->ensureDefaultAccountsExist();

        $this->assertDatabaseHas('accounts', ['code' => '1010', 'name' => 'Cash & Bank']);
        $this->assertDatabaseHas('accounts', ['code' => '1210', 'name' => 'Inventory Asset']);
        $this->assertDatabaseHas('accounts', ['code' => '2010', 'name' => 'Accounts Payable']);
        $this->assertDatabaseHas('accounts', ['code' => '4010', 'name' => 'Sales Revenue']);

        $this->assertEquals(4, Account::count());
    }

    /**
     * Test double-entry balance check error.
     */
    public function test_out_of_balance_journal_entry_throws_exception(): void
    {
        $service = app(AccountingService::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Journal entry out of balance');

        $service->postJournalEntry([
            'reference' => 'Unbalanced',
            'lines' => [
                [
                    'account_code' => '1010',
                    'type' => 'debit',
                    'amount' => 100.00,
                ],
                [
                    'account_code' => '4010',
                    'type' => 'credit',
                    'amount' => 95.00, // unequal!
                ],
            ],
        ]);
    }

    /**
     * Test invoice revenue posting.
     */
    public function test_post_invoice_creates_balanced_journal_entry(): void
    {
        $service = app(AccountingService::class);

        // Setup mock order & payment
        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'service_type' => 'parcel',
            'subtotal' => 150.00,
            'discount_amount' => 0.00,
            'tax_rate' => 0.00,
            'tax_amount' => 0.00,
            'extra_charges' => 0.00,
            'total_amount' => 150.00,
        ]);

        $payment = Payment::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'order_id' => $order->id,
            'amount' => 150.00,
            'payment_method' => 'cash',
            'status' => 'paid',
            'received_by' => $this->user->id,
        ]);

        // Generate Invoice (triggers postInvoice inside BillingService)
        $invoice = app(\App\Services\BillingService::class)->generateInvoice($order, $payment);

        $this->assertNotNull($invoice);
        
        // Assert journal entry created
        $this->assertDatabaseHas('journal_entries', [
            'reference' => 'Invoice #' . $invoice->invoice_number,
        ]);

        $entry = JournalEntry::where('reference', 'Invoice #' . $invoice->invoice_number)->firstOrFail();
        $this->assertEquals(2, $entry->lines()->count());

        $debitLine = $entry->lines()->where('type', 'debit')->firstOrFail();
        $creditLine = $entry->lines()->where('type', 'credit')->firstOrFail();

        $this->assertEquals('1010', $debitLine->account->code);
        $this->assertEquals(150.00, $debitLine->amount);

        $this->assertEquals('4010', $creditLine->account->code);
        $this->assertEquals(150.00, $creditLine->amount);
    }

    /**
     * Test Goods Receipt (GRN) posting.
     */
    public function test_post_goods_receipt_creates_balanced_journal_entry(): void
    {
        $service = app(AccountingService::class);
        $service->ensureDefaultAccountsExist();

        // Setup mock Purchase Order & Goods Receipt
        $po = PurchaseOrder::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-1234',
            'status' => 'sent',
            'ordered_by' => $this->user->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'grocery_item_id' => $this->groceryItem->id,
            'measurement_unit_id' => $this->unit->id,
            'ordered_quantity' => 5,
            'unit_price' => 10.00,
            'total_price' => 50.00,
        ]);

        $receipt = GoodsReceipt::create([
            'purchase_order_id' => $po->id,
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'receipt_date' => now()->toDateString(),
            'received_by' => $this->user->id,
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $receipt->id,
            'purchase_order_item_id' => $poItem->id,
            'grocery_item_id' => $this->groceryItem->id,
            'quantity_received' => 5,
            'unit_cost' => 10.00,
            'total_cost' => 50.00,
        ]);

        // Post Goods Receipt to GL
        $entry = $service->postGoodsReceipt($receipt);

        $this->assertNotNull($entry);
        $this->assertDatabaseHas('journal_entries', [
            'reference' => 'GRN #' . $receipt->id,
        ]);

        $debitLine = $entry->lines()->where('type', 'debit')->firstOrFail();
        $creditLine = $entry->lines()->where('type', 'credit')->firstOrFail();

        $this->assertEquals('1210', $debitLine->account->code); // Inventory Asset
        $this->assertEquals(50.00, $debitLine->amount);

        $this->assertEquals('2010', $creditLine->account->code); // Accounts Payable
        $this->assertEquals(50.00, $creditLine->amount);
    }

    /**
     * Test supplier payment posting.
     */
    public function test_post_supplier_payment_creates_balanced_journal_entry(): void
    {
        $service = app(AccountingService::class);
        $service->ensureDefaultAccountsExist();

        // Record Debit / Payment to Supplier (triggers postSupplierPayment inside SupplierLedgerService)
        $ledger = app(\App\Domains\Procurement\Services\SupplierLedgerService::class)->recordDebit(
            supplier: $this->supplier->id,
            amount: 200.00,
            notes: 'Test Payment'
        );

        $this->assertNotNull($ledger);

        // Assert journal entry created
        $this->assertDatabaseHas('journal_entries', [
            'reference' => 'Payment - ' . $this->supplier->name,
        ]);

        $entry = JournalEntry::where('reference', 'Payment - ' . $this->supplier->name)->firstOrFail();
        
        $debitLine = $entry->lines()->where('type', 'debit')->firstOrFail();
        $creditLine = $entry->lines()->where('type', 'credit')->firstOrFail();

        $this->assertEquals('2010', $debitLine->account->code); // Accounts Payable debited (decreases liability)
        $this->assertEquals(200.00, $debitLine->amount);

        $this->assertEquals('1010', $creditLine->account->code); // Cash credited (decreases asset)
        $this->assertEquals(200.00, $creditLine->amount);
    }
}
