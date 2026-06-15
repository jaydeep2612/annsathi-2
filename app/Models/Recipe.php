<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperRecipe
 */
class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'menu_item_id',
        'item_variant_id',
        'grocery_item_id',
        'measurement_unit_id',
        'quantity_required',
        'version',
        'is_current',
    ];

    protected $casts = [
        'quantity_required' => 'decimal:4',
        'version' => 'integer',
        'is_current' => 'boolean',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class, 'item_variant_id');
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }

    public function measurementUnit(): BelongsTo
    {
        return $this->belongsTo(MeasurementUnit::class);
    }
}
