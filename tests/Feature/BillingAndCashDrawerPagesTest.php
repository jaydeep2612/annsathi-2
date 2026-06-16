<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Shift;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\CustomerSession;
use App\Models\RestaurantTable;
use Livewire\Livewire;
use App\Filament\RestaurantAdmin\Pages\BillingPage;
use App\Filament\RestaurantAdmin\Pages\CashDrawerPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingAndCashDrawerPagesTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Tenant Structure
        $this->restaurant = Restaurant::create([
            'name' => 'Burger Palace',
            'slug' => 'burger-palace',
            'subscription_plan' => 'pro',
            'is_active' => true,
            'settings' => [
                'gst_rate' => 5.0,
                'invoice_prefix' => 'TX',
            ],
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Main Branch',
            'is_active' => true,
        ]);

        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);
        app()->bind('tenant.branch_id', fn() => $this->branch->id);

        $this->manager = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Manager User',
            'email' => 'manager@annsathi.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->actingAs($this->manager);
    }

    /**
     * Test CashDrawerPage loading and operations.
     */
    public function test_cash_drawer_page_operations(): void
    {
        // 1. Verify page loads successfully
        Livewire::test(CashDrawerPage::class)
            ->assertOk()
            ->set('shiftName', 'Morning Shift')
            ->set('shiftType', 'morning')
            ->set('openingBalance', 1500.00)
            ->call('openShift')
            ->assertHasNoErrors();

        // Verify shift and drawer were created in database
        $this->assertDatabaseHas('shifts', [
            'name' => 'Morning Shift',
            'status' => 'open',
        ]);

        $shift = Shift::where('status', 'open')->first();
        $this->assertNotNull($shift);

        $this->assertDatabaseHas('cash_drawers', [
            'shift_id' => $shift->id,
            'opening_balance' => 1500.00,
            'status' => 'open',
        ]);

        // 2. Verify cash movements can be recorded on active shift
        Livewire::test(CashDrawerPage::class)
            ->assertOk()
            ->set('movementType', 'cash_in')
            ->set('movementAmount', 500.00)
            ->set('movementReason', 'Sales float top-up')
            ->call('recordMovement')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cash_movements', [
            'type' => 'cash_in',
            'amount' => 500.00,
            'reason' => 'Sales float top-up',
        ]);

        // 3. Verify shift closing and reconciliation
        Livewire::test(CashDrawerPage::class)
            ->assertOk()
            ->set('closingBalance', 2000.00)
            ->set('closingNotes', 'Reconciliation match')
            ->call('closeShift')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'status' => 'closed',
        ]);

        $drawer = CashDrawer::where('shift_id', $shift->id)->first();
        $this->assertEquals('closed', $drawer->status);
        $this->assertEquals(2000.00, $drawer->closing_balance);
        $this->assertEquals(2000.00, $drawer->expected_closing_balance);
        $this->assertEquals(0.00, $drawer->variance);
    }

    /**
     * Test BillingPage loading and operations.
     */
    public function test_billing_page_operations(): void
    {
        // 1. Open shift first so payment is allowed
        $shift = Shift::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'POS Test Shift',
            'started_by' => $this->manager->id,
            'start_time' => now(),
            'status' => 'open',
        ]);

        $drawer = CashDrawer::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $shift->id,
            'opened_by' => $this->manager->id,
            'opening_balance' => 1000.00,
            'expected_closing_balance' => 1000.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        // Create category and menu item
        $category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Burgers',
            'slug' => 'burgers',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $menuItem = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id' => $category->id,
            'name' => 'Classic Burger',
            'slug' => 'classic-burger',
            'base_price' => 200.00,
            'is_available' => true,
        ]);

        // Create table and customer session
        $table = RestaurantTable::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Table B1',
            'capacity' => 2,
            'qr_token' => 'qr-b1',
            'status' => 'occupied',
        ]);

        $session = CustomerSession::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'session_type' => 'table',
            'session_token' => 'SES-TEST-B1',
            'sessionable_type' => RestaurantTable::class,
            'sessionable_id' => $table->id,
            'customer_name' => 'Bob',
            'status' => 'active',
            'is_primary' => true,
            'shift_id' => $shift->id,
        ]);

        // Create order
        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'customer_session_id' => $session->id,
            'service_type' => 'dine_in',
            'status' => 'served',
            'payment_status' => 'unpaid',
            'subtotal' => 200.00,
            'tax_rate' => 5.0,
            'tax_amount' => 10.00,
            'total_amount' => 210.00,
            'shift_id' => $shift->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'item_name' => 'Classic Burger',
            'quantity' => 1,
            'unit_price' => 200.00,
            'total_price' => 200.00,
            'status' => 'served',
        ]);

        // Test Livewire component
        Livewire::test(BillingPage::class)
            ->assertOk()
            ->call('selectOrder', $order->id)
            ->assertSet('selectedOrderId', $order->id)
            ->assertSet('cashReceived', 210.00)
            ->assertSet('changeAmount', 0.00)
            ->set('paymentMethod', 'cash')
            ->set('releaseTable', true)
            ->call('processPayment')
            ->assertHasNoErrors();

        // Verify order status is completed and paid
        $this->assertEquals('completed', $order->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);

        // Verify invoice and payment were created
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 210.00,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
            'grand_total' => 210.00,
        ]);

        // Verify table session is closed and table is available
        $this->assertEquals('closed', $session->fresh()->status);
        $this->assertEquals('available', $table->fresh()->status);

        // Verify cash movement recorded in drawer
        $this->assertDatabaseHas('cash_movements', [
            'cash_drawer_id' => $drawer->id,
            'type' => 'cash_in',
            'amount' => 210.00,
        ]);
    }
}
