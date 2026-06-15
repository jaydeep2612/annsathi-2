<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperItemVariantGroup
 */
class ItemVariantGroup extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'menu_item_id',
        'name',
        'is_required',
        'min_select',
        'max_select',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'min_select' => 'integer',
        'max_select' => 'integer',
        'sort_order' => 'integer',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ItemVariant::class, 'variant_group_id');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
