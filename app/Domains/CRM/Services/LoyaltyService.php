<?php

declare(strict_types=1);

namespace App\Domains\CRM\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Domains\CRM\Exceptions\CRMException;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Calculate points earned based on order total.
     */
    public function calculatePointsEarned(float $amount, ?int $branchId = null, ?int $restaurantId = null): int
    {
        $ratio = (float) ($this->settingsService->get('loyalty_earn_ratio', $branchId, $restaurantId) ?? 10.0);
        if ($ratio <= 0) {
            return 0;
        }
        return (int) floor($amount / $ratio);
    }

    /**
     * Calculate discount value from points to redeem.
     */
    public function getPointValue(int $points, ?int $branchId = null, ?int $restaurantId = null): float
    {
        $valuePerPoint = (float) ($this->settingsService->get('loyalty_redeem_value', $branchId, $restaurantId) ?? 1.0);
        return $points * $valuePerPoint;
    }

    /**
     * Earn points for an order.
     */
    public function earnPoints(Order $order): ?LoyaltyTransaction
    {
        if (!$order->customer_id) {
            return null;
        }

        return DB::transaction(function () use ($order) {
            $customer = Customer::lockForUpdate()->findOrFail($order->customer_id);

            $points = $this->calculatePointsEarned(
                (float) $order->total_amount,
                $order->branch_id,
                $order->restaurant_id
            );

            if ($points <= 0) {
                return null;
            }

            // Update customer balance
            $customer->increment('loyalty_points', $points);

            // Log transaction
            return LoyaltyTransaction::create([
                'restaurant_id' => $order->restaurant_id,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'type' => 'earn',
                'points' => $points,
                'notes' => "Earned points on Order #{$order->id}",
            ]);
        });
    }

    /**
     * Redeem points on an order. Applies a discount to the order.
     */
    public function redeemPoints(Order $order, int $points): LoyaltyTransaction
    {
        if ($points <= 0) {
            throw CRMException::invalidPoints($points);
        }

        if (!$order->customer_id) {
            throw new \Exception("Cannot redeem loyalty points on an order not bound to a customer.");
        }

        return DB::transaction(function () use ($order, $points) {
            $customer = Customer::lockForUpdate()->findOrFail($order->customer_id);

            if ($customer->loyalty_points < $points) {
                throw CRMException::insufficientPoints($customer->name, $points, $customer->loyalty_points);
            }

            $discount = $this->getPointValue($points, $order->branch_id, $order->restaurant_id);

            // Capped at order remaining total
            $discount = min($discount, (float) $order->total_amount);

            if ($discount <= 0) {
                throw new \Exception("Order total is already zero; cannot redeem points.");
            }

            // Apply discount to order
            $order->update([
                'discount_amount' => $order->discount_amount + $discount,
                'total_amount' => max(0.00, $order->total_amount - $discount),
            ]);

            // Deduct points from customer
            $customer->decrement('loyalty_points', $points);

            // Log transaction
            return LoyaltyTransaction::create([
                'restaurant_id' => $order->restaurant_id,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'type' => 'redeem',
                'points' => $points,
                'notes' => "Redeemed points on Order #{$order->id}",
            ]);
        });
    }
}
