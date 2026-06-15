<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperOrderItem
 */
class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_item_id',
        'item_name',
        'item_variant_label',
        'selected_variant_id',
        'unit_price',
        'quantity',
        'total_price',
        'item_nature',
        'status',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'total_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class, 'selected_variant_id');
    }

    public function kitchenStatus(): HasOne
    {
        return $this->hasOne(OrderItemKitchenStatus::class);
    }
}
