<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperStockTransfer
 */
class StockTransfer extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'from_branch_id',
        'to_branch_id',
        'status',
        'transferred_by',
        'received_by',
        'notes',
    ];

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
