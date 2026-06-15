<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperGroceryItem
 */
class GroceryItem extends Model
{
    use BelongsToRestaurant, HasBranch, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'measurement_unit_id',
        'supplier_id',
        'name',
        'sku',
        'current_stock',
        'low_stock_threshold',
        'reorder_quantity',
        'cost_per_unit',
        'avg_cost_per_unit',
    ];

    protected $casts = [
        'current_stock' => 'decimal:4',
        'low_stock_threshold' => 'decimal:4',
        'reorder_quantity' => 'decimal:4',
        'cost_per_unit' => 'decimal:2',
        'avg_cost_per_unit' => 'decimal:2',
    ];

    public function measurementUnit(): BelongsTo
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }
}
