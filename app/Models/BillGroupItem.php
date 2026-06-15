<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperBillGroupItem
 */
class BillGroupItem extends Model
{
    protected $table = 'bill_group_items';

    protected $fillable = [
        'bill_group_id',
        'order_item_id',
        'quantity',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function billGroup(): BelongsTo
    {
        return $this->belongsTo(BillGroup::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
