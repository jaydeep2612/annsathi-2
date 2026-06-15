<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperDailyInventorySummary
 */
class DailyInventorySummary extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $table = 'daily_inventory_summaries';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'summary_date',
        'grocery_item_id',
        'opening_stock',
        'additions',
        'consumed',
        'waste',
        'adjustments',
        'closing_stock',
        'waste_cost',
        'consumption_cost',
        'computed_at',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'opening_stock' => 'decimal:4',
        'additions' => 'decimal:4',
        'consumed' => 'decimal:4',
        'waste' => 'decimal:4',
        'adjustments' => 'decimal:4',
        'closing_stock' => 'decimal:4',
        'waste_cost' => 'decimal:2',
        'consumption_cost' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }
}
