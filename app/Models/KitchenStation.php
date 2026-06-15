<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperKitchenStation
 */
class KitchenStation extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'name',
        'display_color',
        'printer_ip',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
