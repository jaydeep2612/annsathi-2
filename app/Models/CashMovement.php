<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\ImmutableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperCashMovement
 */
class CashMovement extends Model
{
    use BelongsToRestaurant, ImmutableModel;

    public $timestamps = false;

    protected $fillable = [
        'cash_drawer_id',
        'restaurant_id',
        'type',
        'amount',
        'reason',
        'reference_id',
        'reference_type',
        'recorded_by',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function cashDrawer(): BelongsTo
    {
        return $this->belongsTo(CashDrawer::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
