<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperMenuItem
 */
class MenuItem extends Model
{
    use BelongsToRestaurant, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'category_id',
        'master_menu_item_id',
        'kitchen_station_id',
        'name',
        'slug',
        'description',
        'base_price',
        'image_path',
        'type',
        'item_nature',
        'is_available',
        'is_featured',
        'prep_time_minutes',
        'allergens',
        'sort_order',
        'tax_group_id',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'prep_time_minutes' => 'integer',
        'allergens' => 'array',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function masterMenuItem(): BelongsTo
    {
        return $this->belongsTo(MasterMenuItem::class);
    }

    public function kitchenStation(): BelongsTo
    {
        return $this->belongsTo(KitchenStation::class);
    }

    public function variantGroups(): HasMany
    {
        return $this->hasMany(ItemVariantGroup::class);
    }

    public function branchOverrides(): HasMany
    {
        return $this->hasMany(BranchMenuItem::class);
    }

    public function taxGroup(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Tax\Models\TaxGroup::class);
    }
}
