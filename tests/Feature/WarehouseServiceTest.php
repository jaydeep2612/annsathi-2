<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\GroceryItem;
use App\Models\MeasurementUnit;
use App\Domains\Warehouse\Models\Warehouse;
use App\Domains\Warehouse\Models\WarehouseStock;
use App\Domains\Warehouse\Models\WarehouseMovement;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Domains\Warehouse\Services\WarehouseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class WarehouseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $warehouse;
    protected $groceryItem;
    protected $unit;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Burger Queen',
            'slug' => 'burger-queen',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'North Branch',
            'is_active' => true,
        ]);

        $this->unit = MeasurementUnit::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Kilogram',
            'short_name' => 'kg',
            'conversion_factor' => 1.0,
        ]);

        $this->groceryItem = GroceryItem::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => null, // central item template
            'measurement_unit_id' => $this->unit->id,
            'name' => 'Raw Potatoes',
            'sku' => 'POT-CENTRAL',
            'cost_per_unit' => 10.00,
        ]);

        $this->warehouse = Warehouse::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Main Warehouse',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'WH Manager',
            'email' => 'wh@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($this->user);
    }

    /**
     * Test adding stock to warehouse.
     */
    public function test_add_stock_to_warehouse(): void
    {
        $service = app(WarehouseService::class);

        $service->addStockToWarehouse($this->warehouse->id, $this->groceryItem->id, 100.0, 10.00);

        // Verify warehouse stock
        $stock = WarehouseStock::where('warehouse_id', $this->warehouse->id)
            ->where('grocery_item_id', $this->groceryItem->id)
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(100.0, $stock->quantity);

        // Verify movement
        $movement = WarehouseMovement::where('from_warehouse_id', $this->warehouse->id)
            ->where('transfer_type', 'receipt')
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(100.0, $movement->quantity);
    }

    /**
     * Test dispatching stock to a branch.
     */
    public function test_dispatch_stock_to_branch(): void
    {
        $service = app(WarehouseService::class);

        // 1. Add stock first
        $service->addStockToWarehouse($this->warehouse->id, $this->groceryItem->id, 150.0, 10.00);

        // 2. Dispatch stock to branch
        $service->dispatchStockToBranch($this->warehouse->id, $this->branch->id, $this->groceryItem->id, 50.0);

        // 3. Verify warehouse stock is decremented
        $stock = WarehouseStock::where('warehouse_id', $this->warehouse->id)->first();
        $this->assertEquals(100.0, $stock->quantity);

        // 4. Verify branch stock grocery item exists and has stock
        $branchItem = GroceryItem::where('branch_id', $this->branch->id)
            ->where('sku', 'POT-CENTRAL')
            ->first();

        $this->assertNotNull($branchItem);
        $this->assertEquals(50.0, $branchItem->current_stock);

        // 5. Verify branch FIFO batch was created
        $batch = InventoryBatch::where('branch_id', $this->branch->id)
            ->where('grocery_item_id', $branchItem->id)
            ->first();

        $this->assertNotNull($batch);
        $this->assertEquals(50.0, $batch->current_quantity);
        $this->assertEquals(10.00, $batch->unit_cost);

        // 6. Verify branch inventory transaction was logged
        $transaction = InventoryTransaction::where('branch_id', $this->branch->id)
            ->where('type', 'transfer')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals(50.0, $transaction->quantity);
        $this->assertEquals($batch->id, $transaction->inventory_batch_id);
    }

    /**
     * Test dispatching stock fails if insufficient inventory is in warehouse.
     */
    public function test_dispatch_stock_fails_if_insufficient(): void
    {
        $service = app(WarehouseService::class);

        // Add small stock
        $service->addStockToWarehouse($this->warehouse->id, $this->groceryItem->id, 10.0, 10.00);

        // Dispatch large stock - should fail
        $this->expectException(Exception::class);
        $service->dispatchStockToBranch($this->warehouse->id, $this->branch->id, $this->groceryItem->id, 50.0);
    }
}
