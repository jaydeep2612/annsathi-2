<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperMeasurementUnit
 */
class MeasurementUnit extends Model
{
    use BelongsToRestaurant, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'name',
        'short_name',
        'base_unit',
        'conversion_factor',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
    ];
}
