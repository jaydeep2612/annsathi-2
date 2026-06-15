<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperInventoryTransaction
 */
class InventoryTransaction extends Model
{
    use BelongsToRestaurant, HasBranch, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'grocery_item_id',
        'inventory_batch_id',
        'type',
        'quantity',
        'balance_after',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'notes',
        'performed_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
