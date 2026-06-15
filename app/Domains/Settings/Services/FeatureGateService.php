<?php

declare(strict_types=1);

namespace App\Domains\Settings\Services;

use App\Domains\Settings\Models\FeatureFlag;
use App\Domains\Settings\Models\PlanFeature;
use App\Domains\Settings\Models\RestaurantFeatureOverride;
use App\Models\Restaurant;
use App\Shared\Exceptions\FeatureNotEnabledException;

class FeatureGateService
{
    /**
     * Determine if the restaurant subscription allows this feature.
     */
    public function allows(string $featureKey, ?int $restaurantId = null): bool
    {
        $restaurantId = $restaurantId ?? (app()->bound('tenant.restaurant_id') ? app('tenant.restaurant_id') : null);

        if (!$restaurantId) {
            return false;
        }

        $restaurant = Restaurant::find($restaurantId);
        if (!$restaurant) {
            return false;
        }

        $flag = FeatureFlag::where('key', $featureKey)->first();
        if (!$flag) {
            return false;
        }

        // 1. Check overrides
        $override = RestaurantFeatureOverride::where('restaurant_id', $restaurantId)
            ->where('feature_flag_id', $flag->id)
            ->first();

        if ($override !== null) {
            return (bool) $override->is_enabled;
        }

        // 2. Check Plan Default
        $plan = $restaurant->subscription_plan; // 'trial', 'basic', 'pro', 'enterprise'
        return PlanFeature::where('plan_key', $plan)
            ->where('feature_flag_id', $flag->id)
            ->exists();
    }

    /**
     * Enforce a feature checks, throwing custom domain exception if unauthorized.
     */
    public function check(string $featureKey, ?int $restaurantId = null): void
    {
        if (!$this->allows($featureKey, $restaurantId)) {
            throw new FeatureNotEnabledException($featureKey);
        }
    }
}
