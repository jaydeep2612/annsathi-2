<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperPurchaseOrder
 */
class PurchaseOrder extends Model
{
    use BelongsToRestaurant, HasBranch, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'supplier_id',
        'po_number',
        'status',
        'ordered_by',
        'expected_delivery_date',
        'notes',
        'total_amount',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orderer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
