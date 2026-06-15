<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperWaiterTableAssignment
 */
class WaiterTableAssignment extends Model
{
    protected $table = 'waiter_table_assignments';

    protected $fillable = [
        'user_id',
        'restaurant_table_id',
        'assigned_by',
        'assigned_at',
        'released_at',
        'is_active',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
