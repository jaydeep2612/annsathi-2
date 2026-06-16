<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Customer;
use App\Models\RestaurantTable;
use App\Models\Reservation;
use App\Models\Shift;
use App\Models\CashDrawer;
use App\Models\Order;
use App\Models\Category;
use App\Models\MenuItem;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Sprint9ApiAndSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Pizza House',
            'slug' => 'pizza-house',
            'subscription_plan' => 'pro',
            'is_active' => true,
            'settings' => [
                'gst_rate' => 5.0,
                'invoice_prefix' => 'TX',
            ],
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
            'name' => 'POS Cashier',
            'email' => 'cashier@annsathi.com',
            'password' => bcrypt('password'),
        ]);

        Sanctum::actingAs($this->user);
    }

    /**
     * Test Customer API Endpoints.
     */
    public function test_customer_crm_endpoints(): void
    {
        // 1. Create a customer via API
        $response = $this->postJson('/api/v1/customers', [
            'name' => 'Alice Green',
            'phone' => '9876543210',
            'email' => 'alice@gmail.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Alice Green');

        $this->assertDatabaseHas('customers', [
            'phone' => '9876543210',
            'name' => 'Alice Green',
        ]);

        $customerId = $response->json('data.id');

        // 2. Fetch customers list
        $listResponse = $this->getJson('/api/v1/customers?search=Alice');
        $listResponse->assertStatus(200)
            ->assertJsonFragment(['name' => 'Alice Green']);

        // 3. Get single customer details
        $showResponse = $this->getJson("/api/v1/customers/{$customerId}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Alice Green');
    }

    /**
     * Test Reservations API Endpoints.
     */
    public function test_reservations_endpoints(): void
    {
        $table = RestaurantTable::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Table A1',
            'capacity' => 4,
            'qr_token' => 'qr-a1',
            'status' => 'available',
        ]);

        // 1. Create reservation
        $response = $this->postJson('/api/v1/reservations', [
            'restaurant_table_id' => $table->id,
            'customer_name' => 'John Doe',
            'customer_phone' => '9998887776',
            'reservation_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'pax_count' => 2,
            'duration_minutes' => 60,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.customer_name', 'John Doe');

        $reservationId = $response->json('data.id');

        // 2. List reservations
        $listResponse = $this->getJson('/api/v1/reservations');
        $listResponse->assertStatus(200)
            ->assertJsonFragment(['customer_name' => 'John Doe']);

        // 3. Cancel reservation
        $cancelResponse = $this->postJson("/api/v1/reservations/{$reservationId}/cancel");
        $cancelResponse->assertStatus(200);

        $this->assertEquals('cancelled', Reservation::find($reservationId)->status);
    }

    /**
     * Test Offline Sync API Endpoint.
     */
    public function test_offline_sync_endpoint(): void
    {
        $shift = Shift::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Sync Test Shift',
            'started_by' => $this->user->id,
            'start_time' => now(),
            'status' => 'open',
        ]);

        $drawer = CashDrawer::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $shift->id,
            'opened_by' => $this->user->id,
            'opening_balance' => 500.00,
            'expected_closing_balance' => 500.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Menu',
            'slug' => 'menu',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $menuItem = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id' => $category->id,
            'name' => 'Tacos',
            'slug' => 'tacos',
            'base_price' => 100.00,
            'is_available' => true,
        ]);

        // Send a batch of sync actions
        $response = $this->postJson('/api/v1/sync', [
            'actions' => [
                [
                    'device_identifier' => 'pos-terminal-1',
                    'action_type' => 'sync_sale',
                    'payload' => [
                        'order' => [
                            'service_type' => 'parcel',
                            'shift_id' => $shift->id,
                            'items' => [
                                [
                                    'menu_item_id' => $menuItem->id,
                                    'quantity' => 2,
                                ]
                            ]
                        ],
                        'payment' => [
                            'payment_method' => 'cash',
                            'amount' => 210.00,
                            'shift_id' => $shift->id,
                            'received_by' => $this->user->id,
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.pushed_count', 1)
            ->assertJsonPath('data.sync_results.synced', 1)
            ->assertJsonPath('data.sync_results.failed', 0);

        // Verify order and payment were created and processed
        $this->assertDatabaseHas('orders', [
            'service_type' => 'parcel',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('payments', [
            'payment_method' => 'cash',
            'amount' => 210.00,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('invoices', [
            'grand_total' => 210.00,
        ]);

        $this->assertDatabaseHas('cash_movements', [
            'cash_drawer_id' => $drawer->id,
            'type' => 'cash_in',
            'amount' => 210.00,
        ]);
    }
}
