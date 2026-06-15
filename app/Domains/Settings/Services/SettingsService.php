<?php

declare(strict_types=1);

namespace App\Domains\Settings\Services;

use Illuminate\Support\Facades\Cache;
use App\Domains\Settings\Models\SystemSetting;
use App\Domains\Settings\Models\RestaurantSetting;
use App\Domains\Settings\Models\BranchSetting;

class SettingsService
{
    /**
     * Get a setting by key, checking Branch -> Restaurant -> System cascadingly.
     */
    public function get(string $key, ?int $branchId = null, ?int $restaurantId = null): mixed
    {
        // Default resolver fallback bindings
        $restaurantId = $restaurantId ?? (app()->bound('tenant.restaurant_id') ? app('tenant.restaurant_id') : null);
        $branchId = $branchId ?? (app()->bound('tenant.branch_id') ? app('tenant.branch_id') : null);

        $cacheKey = "setting_{$key}_b{$branchId}_r{$restaurantId}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $branchId, $restaurantId) {
            // 1. Check Branch Setting
            if ($branchId) {
                $branchVal = BranchSetting::where('branch_id', $branchId)->where('key', $key)->first();
                if ($branchVal) {
                    return $this->castValue($branchVal->value);
                }
            }

            // 2. Check Restaurant Setting
            if ($restaurantId) {
                $restVal = RestaurantSetting::where('restaurant_id', $restaurantId)->where('key', $key)->first();
                if ($restVal) {
                    return $this->castValue($restVal->value);
                }
            }

            // 3. Fallback to System Setting
            $sysVal = SystemSetting::where('key', $key)->first();
            return $sysVal ? $this->castValue($sysVal->value) : null;
        });
    }

    /**
     * Update or create a system setting.
     */
    public function setSystem(string $key, string $value, ?string $description = null): SystemSetting
    {
        $setting = SystemSetting::updateOrCreate(['key' => $key], [
            'value' => $value,
            'description' => $description,
        ]);

        $this->clearCache($key);
        return $setting;
    }

    /**
     * Update or create a restaurant setting.
     */
    public function setRestaurant(int $restaurantId, string $key, string $value): RestaurantSetting
    {
        $setting = RestaurantSetting::updateOrCreate([
            'restaurant_id' => $restaurantId,
            'key' => $key,
        ], [
            'value' => $value,
        ]);

        $this->clearCache($key, null, $restaurantId);
        return $setting;
    }

    /**
     * Update or create a branch setting.
     */
    public function setBranch(int $branchId, string $key, string $value): BranchSetting
    {
        $setting = BranchSetting::updateOrCreate([
            'branch_id' => $branchId,
            'key' => $key,
        ], [
            'value' => $value,
        ]);

        $this->clearCache($key, $branchId);
        return $setting;
    }

    /**
     * Clear setting cache files.
     */
    protected function clearCache(string $key, ?int $branchId = null, ?int $restaurantId = null): void
    {
        $restaurantId = $restaurantId ?? (app()->bound('tenant.restaurant_id') ? app('tenant.restaurant_id') : null);
        $branchId = $branchId ?? (app()->bound('tenant.branch_id') ? app('tenant.branch_id') : null);

        Cache::forget("setting_{$key}_b{$branchId}_r{$restaurantId}");
        Cache::forget("setting_{$key}_b_r{$restaurantId}");
        Cache::forget("setting_{$key}_b_r");
    }

    /**
     * Cast string representation to primitive type.
     */
    protected function castValue(string $val): mixed
    {
        if (is_numeric($val)) {
            return str_contains($val, '.') ? (float) $val : (int) $val;
        }
        if ($val === 'true' || $val === 'false') {
            return $val === 'true';
        }
        return $val;
    }
}
