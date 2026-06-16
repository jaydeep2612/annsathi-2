<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\KitchenStation;
use App\Models\KitchenQueue;
use App\Models\OrderItemKitchenStatus;
use App\Services\KitchenRoutingService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KitchenKdsTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $user;
    protected $chef;
    protected $category;
    protected $item;
    protected $station;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Burger Palace',
            'slug' => 'burger-palace',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Main Kitchen',
            'is_active' => true,
        ]);

        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);
        app()->bind('tenant.branch_id', fn() => $this->branch->id);

        $this->user = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'POS Waiter',
            'email' => 'waiter@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->chef = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Main Chef',
            'email' => 'chef@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);

        // Station
        $this->station = KitchenStation::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Burger Station',
            'is_active' => true,
        ]);

        // Category
        $this->category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Burgers',
            'slug' => 'burgers',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Item
        $this->item = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id' => $this->category->id,
            'kitchen_station_id' => $this->station->id,
            'name' => 'Classic Cheeseburger',
            'slug' => 'classic-cheeseburger',
            'base_price' => 120.00,
            'is_available' => true,
        ]);
    }

    /**
     * Test placing order and routing to KDS.
     */
    public function test_route_order_to_kitchen(): void
    {
        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'service_type' => 'parcel',
            'subtotal' => 120.00,
            'total_amount' => 120.00,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'quantity' => 1,
            'unit_price' => 120.00,
            'total_price' => 120.00,
            'status' => 'pending',
        ]);

        $service = app(KitchenRoutingService::class);
        $service->routeOrderToKitchen($order);

        // Verify ticket created in kitchen queue
        $this->assertDatabaseHas('kitchen_queue', [
            'order_id' => $order->id,
            'kitchen_station_id' => $this->station->id,
            'current_status' => 'placed',
        ]);

        // Verify order item status is preparing
        $this->assertEquals('preparing', $orderItem->fresh()->status);

        // Verify detailed status log
        $this->assertDatabaseHas('order_item_kitchen_status', [
            'order_item_id' => $orderItem->id,
            'kitchen_station_id' => $this->station->id,
            'status' => 'queued',
        ]);
    }

    /**
     * Test KDS preparing and ready flow.
     */
    public function test_kds_status_transitions(): void
    {
        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'service_type' => 'parcel',
            'subtotal' => 120.00,
            'total_amount' => 120.00,
            'status' => 'confirmed',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'quantity' => 1,
            'unit_price' => 120.00,
            'total_price' => 120.00,
            'status' => 'pending',
        ]);

        $service = app(KitchenRoutingService::class);
        $service->routeOrderToKitchen($order);

        $statusLog = OrderItemKitchenStatus::where('order_item_id', $orderItem->id)->firstOrFail();

        // 1. Chef claims ticket and starts preparing
        $service->startPreparingItem($statusLog->id, $this->chef->id);

        $this->assertEquals('preparing', $statusLog->fresh()->status);
        $this->assertEquals('preparing', $statusLog->kitchenQueue->fresh()->current_status);
        $this->assertEquals($this->chef->id, $statusLog->kitchenQueue->fresh()->assigned_chef_id);

        // 2. Chef finishes item
        $service->completePreparingItem($statusLog->id);

        $this->assertEquals('ready', $statusLog->fresh()->status);
        $this->assertEquals('ready', $statusLog->kitchenQueue->fresh()->current_status);
        $this->assertEquals('ready', $orderItem->fresh()->status);
        
        // Assert overall order transitions to ready status
        $this->assertEquals('ready', $order->fresh()->status);
    }
}
