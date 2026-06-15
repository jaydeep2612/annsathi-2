<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperStockTransferItem
 */
class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'grocery_item_id',
        'quantity',
        'received_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'received_quantity' => 'decimal:4',
    ];

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }
}
