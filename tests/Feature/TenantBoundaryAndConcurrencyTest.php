<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\CustomerSession;
use App\Models\ApprovalRequest;
use App\Models\RestaurantTable;
use App\Services\OrderService;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Exception;

class TenantBoundaryAndConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant1;
    protected $restaurant2;
    protected $branch1;
    protected $branch2;
    protected $manager1;
    protected $manager2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web']);

        // Create Restaurant 1
        $this->restaurant1 = Restaurant::create([
            'name' => 'Pizza House',
            'slug' => 'pizza-house',
            'subscription_plan' => 'pro',
            'features' => [],
            'settings' => ['gst_rate' => 5.0, 'require_waiter_assignment' => false],
            'user_limits' => 5,
            'table_limits' => 5,
            'is_active' => true,
        ]);

        $this->branch1 = Branch::create([
            'restaurant_id' => $this->restaurant1->id,
            'name' => 'Branch One',
            'is_active' => true,
        ]);

        $this->manager1 = User::create([
            'restaurant_id' => $this->restaurant1->id,
            'branch_id' => $this->branch1->id,
            'name' => 'R1 Manager',
            'email' => 'm1@pizza.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->manager1->assignRole('manager');

        // Create Restaurant 2
        $this->restaurant2 = Restaurant::create([
            'name' => 'Burger Palace',
            'slug' => 'burger-palace',
            'subscription_plan' => 'pro',
            'features' => [],
            'settings' => ['gst_rate' => 5.0, 'require_waiter_assignment' => false],
            'user_limits' => 5,
            'table_limits' => 5,
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'restaurant_id' => $this->restaurant2->id,
            'name' => 'Branch Two',
            'is_active' => true,
        ]);

        $this->manager2 = User::create([
            'restaurant_id' => $this->restaurant2->id,
            'branch_id' => $this->branch2->id,
            'name' => 'R2 Manager',
            'email' => 'm2@burger.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->manager2->assignRole('manager');
    }

    /**
     * Test that database operations are strictly scoped per tenant
     */
    public function test_tenant_boundary_isolation(): void
    {
        // Bind R1 context and create a category
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant1->id);
        app()->bind('tenant.branch_id', fn() => $this->branch1->id);

        $cat1 = Category::create([
            'restaurant_id' => $this->restaurant1->id,
            'name' => 'Pizzas',
            'slug' => 'pizzas',
            'is_active' => true,
        ]);

        // Unbind and bind R2 context and create a category
        app()->offsetUnset('tenant.restaurant_id');
        app()->offsetUnset('tenant.branch_id');
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant2->id);
        app()->bind('tenant.branch_id', fn() => $this->branch2->id);

        $cat2 = Category::create([
            'restaurant_id' => $this->restaurant2->id,
            'name' => 'Burgers',
            'slug' => 'burgers',
            'is_active' => true,
        ]);

        // Query categories under R2 context - should only see Burgers
        $categoriesR2 = Category::all();
        $this->assertCount(1, $categoriesR2);
        $this->assertEquals('Burgers', $categoriesR2->first()->name);

        // Switch to R1 context - should only see Pizzas
        app()->offsetUnset('tenant.restaurant_id');
        app()->offsetUnset('tenant.branch_id');
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant1->id);
        app()->bind('tenant.branch_id', fn() => $this->branch1->id);

        $categoriesR1 = Category::all();
        $this->assertCount(1, $categoriesR1);
        $this->assertEquals('Pizzas', $categoriesR1->first()->name);
    }

    /**
     * Test that Customer APIs are properly scoped by request headers
     */
    public function test_tenant_api_routing_boundary(): void
    {
        // Set up category in R1
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant1->id);
        Category::create([
            'restaurant_id' => $this->restaurant1->id,
            'name' => 'Pizzas',
            'slug' => 'pizzas',
            'is_active' => true,
        ]);
        app()->offsetUnset('tenant.restaurant_id');

        // Set up category in R2
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant2->id);
        Category::create([
            'restaurant_id' => $this->restaurant2->id,
            'name' => 'Burgers',
            'slug' => 'burgers',
            'is_active' => true,
        ]);
        app()->offsetUnset('tenant.restaurant_id');

        // Call R1 API
        $response1 = $this->withHeaders([
            'X-Restaurant-ID' => $this->restaurant1->id,
        ])->getJson('/api/v1/menu');

        $response1->assertStatus(200);
        $data1 = $response1->json('data');
        $this->assertCount(1, $data1);
        $this->assertEquals('Pizzas', $data1[0]['name']);

        // Call R2 API
        $response2 = $this->withHeaders([
            'X-Restaurant-ID' => $this->restaurant2->id,
        ])->getJson('/api/v1/menu');

        $response2->assertStatus(200);
        $data2 = $response2->json('data');
        $this->assertCount(1, $data2);
        $this->assertEquals('Burgers', $data2[0]['name']);
    }

    /**
     * Test Pessimistic locking behavior using Db transaction locks
     */
    public function test_pessimistic_locking_concurrency(): void
    {
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant1->id);
        app()->bind('tenant.branch_id', fn() => $this->branch1->id);

        $table = RestaurantTable::create([
            'restaurant_id' => $this->restaurant1->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Table T-Conc',
            'capacity' => 4,
            'qr_token' => 'qr-t-conc',
            'status' => 'available',
        ]);

        $sessionService = app(\App\Services\SessionService::class);
        $session = $sessionService->startSession([
            'session_type' => 'table',
            'sessionable_id' => $table->id,
            'customer_name' => 'Bob',
            'pax_count' => 1,
        ]);

        $order = Order::create([
            'restaurant_id' => $this->restaurant1->id,
            'branch_id' => $this->branch1->id,
            'customer_session_id' => $session->id,
            'service_type' => 'dine_in',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_amount' => 100.00,
        ]);

        // Simulating lockForUpdate inside transaction
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::lockForUpdate()->find($order->id);
            $this->assertEquals($order->id, $lockedOrder->id);
            $this->assertEquals('pending', $lockedOrder->status);
        });
    }

    /**
     * Test role hierarchy permissions on approval engine
     */
    public function test_approval_role_restrictions(): void
    {
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant1->id);
        app()->bind('tenant.branch_id', fn() => $this->branch1->id);

        $waiter = User::create([
            'restaurant_id' => $this->restaurant1->id,
            'branch_id' => $this->branch1->id,
            'name' => 'R1 Waiter',
            'email' => 'w1@pizza.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $waiter->assignRole('waiter');

        $req = ApprovalRequest::create([
            'restaurant_id' => $this->restaurant1->id,
            'branch_id' => $this->branch1->id,
            'entity_type' => Order::class,
            'entity_id' => 1,
            'action' => 'void_order',
            'reason' => 'Duplicate entry',
            'requested_by' => $waiter->id,
            'status' => 'pending',
        ]);

        $approvalService = app(ApprovalService::class);

        // Waiter attempts to approve - should fail
        $this->actingAs($waiter);
        try {
            $approvalService->approveRequest($req->id, $waiter->id);
            $this->fail("Waiter was able to approve manager request.");
        } catch (Exception $e) {
            if ($e instanceof \PHPUnit\Framework\AssertionFailedError) {
                throw $e;
            }
            $this->assertStringContainsString('Only managers are allowed to approve requests', $e->getMessage());
        }

        // Manager attempts to approve - should succeed
        $this->actingAs($this->manager1);
        $approvedReq = $approvalService->approveRequest($req->id, $this->manager1->id);
        $this->assertEquals('approved', $approvedReq->status);
        $this->assertEquals($this->manager1->id, $approvedReq->approved_by);
    }

    /**
     * Test unified login page access and redirects based on roles
     */
    public function test_unified_login_role_redirections(): void
    {
        // 1. Unauthenticated gets 200 on login page
        $response = $this->get('/login');
        $response->assertStatus(200);

        // 2. Login as Manager redirects to /manager
        $response = $this->post('/login', [
            'email' => 'm1@pizza.com',
            'password' => 'password',
        ]);
        $response->assertRedirect('/manager');

        // Logout
        $this->post('/logout');

        // 3. Login as Chef redirects to /kitchen
        Role::firstOrCreate(['name' => 'chef', 'guard_name' => 'web']);
        $chef = User::create([
            'restaurant_id' => $this->restaurant1->id,
            'branch_id' => $this->branch1->id,
            'name' => 'R1 Chef',
            'email' => 'chef1@pizza.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $chef->assignRole('chef');

        $response = $this->post('/login', [
            'email' => 'chef1@pizza.com',
            'password' => 'password',
        ]);
        $response->assertRedirect('/kitchen');
    }
}
