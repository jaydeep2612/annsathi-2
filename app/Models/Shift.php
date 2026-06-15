<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperShift
 */
class Shift extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'name',
        'shift_type',
        'started_by',
        'ended_by',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function ender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(ShiftStaff::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CustomerSession::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function cashDrawer(): HasOne
    {
        return $this->hasOne(CashDrawer::class);
    }
}
