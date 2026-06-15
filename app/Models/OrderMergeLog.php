<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperOrderMergeLog
 */
class OrderMergeLog extends Model
{
    use BelongsToRestaurant;

    protected $table = 'order_merge_logs';

    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'parent_order_id',
        'merged_order_id',
        'merged_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function mergedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'merged_order_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by');
    }
}
