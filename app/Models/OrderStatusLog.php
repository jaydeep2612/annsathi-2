<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperOrderStatusLog
 */
class OrderStatusLog extends Model
{
    protected $table = 'order_status_logs';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'changed_by',
        'from_status',
        'to_status',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
