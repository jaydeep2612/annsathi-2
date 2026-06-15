<?php

declare(strict_types=1);

namespace App\Domains\Settings\Services;

use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Domains\Settings\Models\UsageMetric;
use App\Shared\Exceptions\QuotaExceededException;

class UsageMeteringService
{
    /**
     * Check if the restaurant can add another branch.
     */
    public function verifyBranchQuota(int $restaurantId): void
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $currentBranches = Branch::where('restaurant_id', $restaurantId)->count();

        if ($currentBranches >= $restaurant->max_branches) {
            throw new QuotaExceededException('branches', (int) $restaurant->max_branches);
        }
    }

    /**
     * Check if the restaurant can add another staff member.
     */
    public function verifyUserQuota(int $restaurantId): void
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $currentUsers = User::where('restaurant_id', $restaurantId)->count();

        if ($currentUsers >= $restaurant->user_limits) {
            throw new QuotaExceededException('users', (int) $restaurant->user_limits);
        }
    }

    /**
     * Check if the restaurant can place another order this month.
     */
    public function verifyOrderQuota(int $restaurantId): void
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $planLimits = [
            'trial' => 100,
            'basic' => 500,
            'pro' => 5000,
            'enterprise' => 9999999,
        ];

        $plan = $restaurant->subscription_plan;
        $limit = $planLimits[$plan] ?? 100;

        $metric = UsageMetric::firstOrCreate([
            'restaurant_id' => $restaurantId,
            'metric_key' => 'monthly_orders_count',
        ], [
            'usage_count' => 0,
            'reset_at' => now()->endOfMonth(),
        ]);

        if ($metric->reset_at && $metric->reset_at->isPast()) {
            $metric->update([
                'usage_count' => 0,
                'reset_at' => now()->endOfMonth(),
            ]);
        }

        if ($metric->usage_count >= $limit) {
            throw new QuotaExceededException('orders_monthly', $limit);
        }
    }

    /**
     * Increment the order usage count for the month.
     */
    public function incrementOrderCount(int $restaurantId): void
    {
        $metric = UsageMetric::firstOrCreate([
            'restaurant_id' => $restaurantId,
            'metric_key' => 'monthly_orders_count',
        ], [
            'usage_count' => 0,
            'reset_at' => now()->endOfMonth(),
        ]);

        $metric->increment('usage_count');
    }
}
