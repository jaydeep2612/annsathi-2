<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperItemSalesSummary
 */
class ItemSalesSummary extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $table = 'item_sales_summaries';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'summary_date',
        'menu_item_id',
        'item_variant_id',
        'quantity_sold',
        'gross_revenue',
        'food_cost',
        'gross_profit',
        'food_cost_pct',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'quantity_sold' => 'integer',
        'gross_revenue' => 'decimal:2',
        'food_cost' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'food_cost_pct' => 'decimal:2',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class, 'item_variant_id');
    }
}
