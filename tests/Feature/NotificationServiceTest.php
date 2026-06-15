<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Domains\Notifications\Models\NotificationChannel;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Notifications\Models\NotificationTemplate;
use App\Domains\Notifications\Models\NotificationsLog;
use App\Domains\Notifications\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Main Branch',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
    }

    /**
     * Test notification dispatch using global fallback template
     */
    public function test_dispatch_with_global_template(): void
    {
        // 1. Setup global channel config
        NotificationChannel::create([
            'name' => 'email',
            'driver' => 'log',
            'is_active' => true,
        ]);

        // 2. Setup global template (restaurant_id is null)
        NotificationTemplate::create([
            'restaurant_id' => null,
            'event_name' => 'order_placed',
            'title' => 'Order #{{order_id}} Placed',
            'body' => 'Hi {{customer_name}}, your order has been received.',
            'channels' => ['email'],
            'is_active' => true,
        ]);

        $service = app(NotificationService::class);

        // Dispatch
        $service->dispatch(
            'order_placed',
            ['order_id' => '999', 'customer_name' => 'Alice'],
            $this->user
        );

        // Assert log was written to database
        $log = NotificationsLog::where('user_id', $this->user->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('Order #999 Placed', $log->title);
        $this->assertEquals('Hi Alice, your order has been received.', $log->body);
        $this->assertEquals('email', $log->type);
        $this->assertEquals('sent', $log->data['status']);
        $this->assertEquals('john@example.com', $log->data['recipient_contact']);
    }

    /**
     * Test notification dispatch using tenant-specific override template
     */
    public function test_dispatch_with_tenant_override_template(): void
    {
        // 1. Setup global channel config
        NotificationChannel::create([
            'name' => 'email',
            'driver' => 'log',
            'is_active' => true,
        ]);

        // 2. Setup global template
        NotificationTemplate::create([
            'restaurant_id' => null,
            'event_name' => 'order_placed',
            'title' => 'Global Title',
            'body' => 'Global Body',
            'channels' => ['email'],
            'is_active' => true,
        ]);

        // 3. Setup tenant override template
        NotificationTemplate::create([
            'restaurant_id' => $this->restaurant->id,
            'event_name' => 'order_placed',
            'title' => 'Custom Restaurant Title: {{order_id}}',
            'body' => 'Custom Restaurant Body',
            'channels' => ['email'],
            'is_active' => true,
        ]);

        $service = app(NotificationService::class);

        // Bind tenant context
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);

        // Dispatch
        $service->dispatch(
            'order_placed',
            ['order_id' => '123'],
            $this->user
        );

        // Assert override was used
        $log = NotificationsLog::where('restaurant_id', $this->restaurant->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('Custom Restaurant Title: 123', $log->title);
        $this->assertEquals('Custom Restaurant Body', $log->body);
    }

    /**
     * Test user notification preferences override defaults
     */
    public function test_dispatch_respects_user_preferences(): void
    {
        // 1. Setup channel configs
        NotificationChannel::create([
            'name' => 'email',
            'driver' => 'log',
            'is_active' => true,
        ]);

        NotificationChannel::create([
            'name' => 'sms',
            'driver' => 'log',
            'is_active' => true,
        ]);

        // 2. Setup template with default email channel
        NotificationTemplate::create([
            'restaurant_id' => $this->restaurant->id,
            'event_name' => 'low_stock',
            'title' => 'Low Stock Alert',
            'body' => 'Item {{item}} is low.',
            'channels' => ['email'],
            'is_active' => true,
        ]);

        // 3. Setup user preference specifying SMS channel instead of email
        NotificationPreference::create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->user->id,
            'event_name' => 'low_stock',
            'channels' => ['sms'],
        ]);

        $service = app(NotificationService::class);
        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);

        // Dispatch
        $service->dispatch(
            'low_stock',
            ['item' => 'Flour'],
            $this->user
        );

        // Assert that only SMS channel log was created
        $logs = NotificationsLog::where('user_id', $this->user->id)->get();
        $this->assertCount(1, $logs);
        $this->assertEquals('sms', $logs->first()->type);
    }
}
