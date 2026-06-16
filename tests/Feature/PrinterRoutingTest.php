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
use App\Models\Printer;
use App\Models\PrinterGroup;
use App\Models\PrinterRoute;
use App\Models\PrintJob;
use App\Domains\Printing\Services\PrinterRoutingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrinterRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $user;
    protected $category;
    protected $item1;
    protected $item2;
    protected $station1;
    protected $station2;
    protected $printer1;
    protected $printer2;
    protected $group1;
    protected $group2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Pizza World',
            'slug' => 'pizza-world',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'East Side',
            'is_active' => true,
        ]);

        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);
        app()->bind('tenant.branch_id', fn() => $this->branch->id);

        $this->user = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'POS Cashier',
            'email' => 'pos@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);

        // Stations
        $this->station1 = KitchenStation::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Oven Station',
            'is_active' => true,
        ]);

        $this->station2 = KitchenStation::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Beverage Station',
            'is_active' => true,
        ]);

        // Category
        $this->category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Main Course',
            'slug' => 'main-course',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Items
        $this->item1 = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id' => $this->category->id,
            'kitchen_station_id' => $this->station1->id,
            'name' => 'Pepperoni Pizza',
            'slug' => 'pepperoni-pizza',
            'base_price' => 250.00,
            'is_available' => true,
        ]);

        $this->item2 = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id' => $this->category->id,
            'kitchen_station_id' => $this->station2->id,
            'name' => 'Fresh Lime Soda',
            'slug' => 'fresh-lime-soda',
            'base_price' => 60.00,
            'is_available' => true,
        ]);

        // Printers
        $this->printer1 = Printer::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Kitchen Printer 1',
            'connection_type' => 'network',
            'ip_address' => '192.168.1.100',
            'is_active' => true,
        ]);

        $this->printer2 = Printer::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Bar Printer',
            'connection_type' => 'network',
            'ip_address' => '192.168.1.101',
            'is_active' => true,
        ]);

        // Printer Groups
        $this->group1 = PrinterGroup::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Pizza KOT Group',
        ]);
        $this->group1->printers()->attach($this->printer1->id);

        $this->group2 = PrinterGroup::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Drinks KOT Group',
        ]);
        $this->group2->printers()->attach($this->printer2->id);

        // Printer Routes
        PrinterRoute::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'kitchen_station_id' => $this->station1->id,
            'printer_group_id' => $this->group1->id,
            'route_type' => 'kot',
            'is_active' => true,
        ]);

        PrinterRoute::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'kitchen_station_id' => $this->station2->id,
            'printer_group_id' => $this->group2->id,
            'route_type' => 'kot',
            'is_active' => true,
        ]);
    }

    /**
     * Test KOT routing to station-specific printers.
     */
    public function test_route_order_kot(): void
    {
        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'service_type' => 'parcel',
            'subtotal' => 310.00,
            'total_amount' => 310.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $this->item1->id,
            'item_name' => $this->item1->name,
            'quantity' => 1,
            'unit_price' => 250.00,
            'total_price' => 250.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $this->item2->id,
            'item_name' => $this->item2->name,
            'quantity' => 1,
            'unit_price' => 60.00,
            'total_price' => 60.00,
        ]);

        $service = app(PrinterRoutingService::class);
        $service->routeOrderKOT($order);

        // Verify two print jobs created (one for printer1 and one for printer2)
        $this->assertDatabaseHas('print_jobs', [
            'printer_id' => $this->printer1->id,
            'title' => "KOT - Order #{$order->id}",
        ]);

        $this->assertDatabaseHas('print_jobs', [
            'printer_id' => $this->printer2->id,
            'title' => "KOT - Order #{$order->id}",
        ]);
    }

    /**
     * Test receipt routing to billing printers.
     */
    public function test_route_order_receipt(): void
    {
        // Add billing route
        PrinterRoute::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'printer_group_id' => $this->group1->id,
            'route_type' => 'receipt',
            'is_active' => true,
        ]);

        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'service_type' => 'parcel',
            'subtotal' => 250.00,
            'total_amount' => 250.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $this->item1->id,
            'item_name' => $this->item1->name,
            'quantity' => 1,
            'unit_price' => 250.00,
            'total_price' => 250.00,
        ]);

        $service = app(PrinterRoutingService::class);
        $service->routeOrderReceipt($order);

        $this->assertDatabaseHas('print_jobs', [
            'printer_id' => $this->printer1->id,
            'title' => "Receipt - Order #{$order->id}",
        ]);
    }
}
