<?php

namespace App\Services;

class TenantResolver
{
    /**
     * Set the current restaurant ID in the app container.
     */
    public function setRestaurantId(int $restaurantId): void
    {
        app()->instance('tenant.restaurant_id', $restaurantId);
    }

    /**
     * Set the current branch ID in the app container.
     */
    public function setBranchId(int $branchId): void
    {
        app()->instance('tenant.branch_id', $branchId);
    }

    /**
     * Get the current restaurant ID.
     */
    public function getRestaurantId(): ?int
    {
        return app()->bound('tenant.restaurant_id') ? app('tenant.restaurant_id') : null;
    }

    /**
     * Get the current branch ID.
     */
    public function getBranchId(): ?int
    {
        return app()->bound('tenant.branch_id') ? app('tenant.branch_id') : null;
    }
}
