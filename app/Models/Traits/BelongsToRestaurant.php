<?php

namespace App\Models\Traits;

use App\Models\Scopes\RestaurantScope;

trait BelongsToRestaurant
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToRestaurant(): void
    {
        static::addGlobalScope(new RestaurantScope);

        static::creating(function ($model) {
            if (app()->bound('tenant.restaurant_id') && ! $model->restaurant_id) {
                $model->restaurant_id = app('tenant.restaurant_id');
            }
        });
    }

    /**
     * Get the restaurant that owns the model.
     */
    public function restaurant()
    {
        return $this->belongsTo(\App\Models\Restaurant::class, 'restaurant_id');
    }
}
