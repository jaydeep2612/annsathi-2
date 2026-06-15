<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperMasterMenuItem
 */
class MasterMenuItem extends Model
{
    use BelongsToRestaurant, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'base_price',
        'type',
        'item_nature',
        'allergens',
        'prep_time_minutes',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'allergens' => 'array',
        'prep_time_minutes' => 'integer',
        'is_active' => 'boolean',
    ];
}
