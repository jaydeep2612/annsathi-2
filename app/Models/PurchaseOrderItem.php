<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPurchaseOrderItem
 */
class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'grocery_item_id',
        'measurement_unit_id',
        'ordered_quantity',
        'received_quantity',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:4',
        'received_quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }

    public function measurementUnit(): BelongsTo
    {
        return $this->belongsTo(MeasurementUnit::class);
    }
}
