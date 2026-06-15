<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperItemVariant
 */
class ItemVariant extends Model
{
    protected $fillable = [
        'variant_group_id',
        'menu_item_id',
        'label',
        'price_modifier',
        'price_type',
        'quantity_value',
        'quantity_unit',
        'affects_inventory',
        'sort_order',
        'is_available',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'quantity_value' => 'decimal:3',
        'affects_inventory' => 'boolean',
        'sort_order' => 'integer',
        'is_available' => 'boolean',
    ];

    public function variantGroup(): BelongsTo
    {
        return $this->belongsTo(ItemVariantGroup::class, 'variant_group_id');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
