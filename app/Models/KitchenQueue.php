<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperKitchenQueue
 */
class KitchenQueue extends Model
{
    use HasBranch;

    protected $table = 'kitchen_queue';

    protected $fillable = [
        'order_id',
        'kitchen_station_id',
        'branch_id',
        'priority',
        'current_status',
        'assigned_chef_id',
        'acknowledged_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function kitchenStation(): BelongsTo
    {
        return $this->belongsTo(KitchenStation::class);
    }

    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_chef_id');
    }

    public function itemStatuses(): HasMany
    {
        return $this->hasMany(OrderItemKitchenStatus::class);
    }
}
