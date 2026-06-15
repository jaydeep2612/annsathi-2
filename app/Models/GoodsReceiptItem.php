<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperGoodsReceiptItem
 */
class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'grocery_item_id',
        'quantity_received',
        'unit_cost',
        'total_cost',
        'batch_number',
        'expiry_date',
        'quality_status',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }
}
