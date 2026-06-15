<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperOrderItemKitchenStatus
 */
class OrderItemKitchenStatus extends Model
{
    protected $table = 'order_item_kitchen_status';

    protected $fillable = [
        'order_item_id',
        'kitchen_station_id',
        'kitchen_queue_id',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function kitchenStation(): BelongsTo
    {
        return $this->belongsTo(KitchenStation::class);
    }

    public function kitchenQueue(): BelongsTo
    {
        return $this->belongsTo(KitchenQueue::class);
    }
}
