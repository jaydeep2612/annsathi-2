<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\Promotion;
use App\Models\OrderPromotion;
use App\Domains\CRM\Services\LoyaltyService;
use App\Domains\CRM\Services\PromotionService;
use App\Domains\CRM\Exceptions\CRMException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class CrmAndPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $branch;
    protected $user;
    protected $customer;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Dessert Hub',
            'slug' => 'dessert-hub',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Mall Road POS',
            'is_active' => true,
        ]);

        app()->bind('tenant.restaurant_id', fn() => $this->restaurant->id);
        app()->bind('tenant.branch_id', fn() => $this->branch->id);

        $this->customer = Customer::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Jane Doe',
            'phone' => '9999999999',
            'email' => 'jane@example.com',
            'loyalty_points' => 0,
        ]);

        $this->category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Ice Creams',
            'slug' => 'ice-creams',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->user = User::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);
    }

    /**
     * Test point accruals on completed orders.
     */
    public function test_loyalty_point_earning_on_order(): void
    {
        $loyaltyService = app(LoyaltyService::class);

        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'service_type' => 'parcel',
            'subtotal' => 125.00,
            'total_amount' => 125.00,
        ]);

        $tx = $loyaltyService->earnPoints($order);

        $this->assertNotNull($tx);
        $this->assertEquals('earn', $tx->type);
        $this->assertEquals(12, $tx->points); // 125 / 10 = 12 points
        $this->assertEquals(12, $this->customer->fresh()->loyalty_points);
    }

    /**
     * Test points redemption applying discount to orders.
     */
    public function test_loyalty_point_redemption_on_order(): void
    {
        $loyaltyService = app(LoyaltyService::class);

        // Give points first
        $this->customer->update(['loyalty_points' => 50]);

        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'service_type' => 'parcel',
            'subtotal' => 100.00,
            'total_amount' => 100.00,
        ]);

        $tx = $loyaltyService->redeemPoints($order, 30);

        $this->assertNotNull($tx);
        $this->assertEquals('redeem', $tx->type);
        $this->assertEquals(30, $tx->points);
        $this->assertEquals(20, $this->customer->fresh()->loyalty_points);
        $this->assertEquals(30.00, $order->fresh()->discount_amount);
        $this->assertEquals(70.00, $order->fresh()->total_amount);
    }

    /**
     * Test redemption validation throws exception if points are insufficient.
     */
    public function test_loyalty_point_redemption_fails_for_insufficient_points(): void
    {
        $loyaltyService = app(LoyaltyService::class);
        $this->customer->update(['loyalty_points' => 10]);

        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'service_type' => 'parcel',
            'subtotal' => 100.00,
            'total_amount' => 100.00,
        ]);

        $this->expectException(CRMException::class);
        $this->expectExceptionMessage('insufficient loyalty points');
        $loyaltyService->redeemPoints($order, 20);
    }

    /**
     * Test flat rate discount coupons.
     */
    public function test_apply_flat_coupon(): void
    {
        $promoService = app(PromotionService::class);

        $promo = Promotion::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Flat 15 Discount',
            'code' => 'FLAT15',
            'type' => 'flat',
            'value' => 15.00,
            'min_order_amount' => 50.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'service_type' => 'parcel',
            'subtotal' => 60.00,
            'total_amount' => 60.00,
        ]);

        $promoService->applyCoupon($order, 'FLAT15');

        $this->assertEquals(15.00, $order->fresh()->discount_amount);
        $this->assertEquals(45.00, $order->fresh()->total_amount);

        $this->assertDatabaseHas('order_promotions', [
            'order_id' => $order->id,
            'promotion_id' => $promo->id,
            'discount_amount' => 15.00,
        ]);
    }

    /**
     * Test percentage coupons with maximum discount caps.
     */
    public function test_apply_percentage_coupon_with_capping(): void
    {
        $promoService = app(PromotionService::class);

        $promo = Promotion::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => '20% Off Capped at 15',
            'code' => 'SAVE20',
            'type' => 'percent',
            'value' => 20.00, // 20%
            'min_order_amount' => 50.00,
            'max_discount_amount' => 15.00,
            'is_active' => true,
        ]);

        // Scenario A: Order subtotal 60.00. 20% of 60 is 12.00. Less than 15 cap, so full 12.00 applies.
        $orderA = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'service_type' => 'parcel',
            'subtotal' => 60.00,
            'total_amount' => 60.00,
        ]);

        $promoService->applyCoupon($orderA, 'SAVE20');
        $this->assertEquals(12.00, $orderA->fresh()->discount_amount);
        $this->assertEquals(48.00, $orderA->fresh()->total_amount);

        // Scenario B: Order subtotal 100.00. 20% of 100 is 20.00. Capped at 15.00, so only 15.00 applies.
        $orderB = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'service_type' => 'parcel',
            'subtotal' => 100.00,
            'total_amount' => 100.00,
        ]);

        $promoService->applyCoupon($orderB, 'SAVE20');
        $this->assertEquals(15.00, $orderB->fresh()->discount_amount);
        $this->assertEquals(85.00, $orderB->fresh()->total_amount);
    }

    /**
     * Test BOGO coupons (Buy item A, Get item B free).
     */
    public function test_apply_bogo_coupon(): void
    {
        $promoService = app(PromotionService::class);

        $itemA = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id' => $this->category->id,
            'name' => 'Waffle Cone Large',
            'slug' => 'waffle-cone-l',
            'base_price' => 150.00,
            'is_available' => true,
        ]);

        $itemB = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id' => $this->category->id,
            'name' => 'Choco Chip Topping',
            'slug' => 'choco-chip-top',
            'base_price' => 50.00,
            'is_available' => true,
        ]);

        $promo = Promotion::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Waffle free toppings BOGO',
            'code' => 'BOGOWAFFLE',
            'type' => 'bogo',
            'bogo_buy_menu_item_id' => $itemA->id,
            'bogo_get_menu_item_id' => $itemB->id,
            'is_active' => true,
        ]);

        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'service_type' => 'parcel',
            'subtotal' => 350.00,
            'total_amount' => 350.00,
        ]);

        // Add 2 Waffles, 1 Choco Chip (Buy 2 waffles, get 1 choco chip free)
        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $itemA->id,
            'item_name' => $itemA->name,
            'quantity' => 2,
            'unit_price' => 150.00,
            'total_price' => 300.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $itemB->id,
            'item_name' => $itemB->name,
            'quantity' => 1,
            'unit_price' => 50.00,
            'total_price' => 50.00,
        ]);

        $promoService->applyCoupon($order, 'BOGOWAFFLE');

        // Discount should be unit price of item B (50.00)
        $this->assertEquals(50.00, $order->fresh()->discount_amount);
        $this->assertEquals(300.00, $order->fresh()->total_amount);
    }
}
