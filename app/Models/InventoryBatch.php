<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperInventoryBatch
 */
class InventoryBatch extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'grocery_item_id',
        'batch_number',
        'supplier_id',
        'initial_quantity',
        'current_quantity',
        'unit_cost',
        'received_date',
        'expiry_date',
    ];

    protected $casts = [
        'initial_quantity' => 'decimal:4',
        'current_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'received_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
