<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTableTransferLog
 */
class TableTransferLog extends Model
{
    use BelongsToRestaurant;

    protected $table = 'table_transfer_logs';

    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'customer_session_id',
        'from_table_id',
        'to_table_id',
        'transferred_by',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function customerSession(): BelongsTo
    {
        return $this->belongsTo(CustomerSession::class);
    }

    public function fromTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'from_table_id');
    }

    public function toTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'to_table_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
