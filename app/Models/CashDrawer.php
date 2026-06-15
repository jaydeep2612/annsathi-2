<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperCashDrawer
 */
class CashDrawer extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'shift_id',
        'opened_by',
        'closed_by',
        'opening_balance',
        'closing_balance',
        'expected_closing_balance',
        'variance',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_closing_balance' => 'decimal:2',
        'variance' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }
}
