<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperCustomerSession
 */
class CustomerSession extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $table = 'customer_sessions';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'session_type',
        'session_token',
        'sessionable_type',
        'sessionable_id',
        'host_session_id',
        'customer_name',
        'customer_phone',
        'pax_count',
        'status',
        'is_primary',
        'join_status',
        'expires_at',
        'check_in_at',
        'check_out_at',
        'actual_checkout_at',
        'closed_at',
        'closed_by',
        'shift_id',
    ];

    protected $casts = [
        'pax_count' => 'integer',
        'is_primary' => 'boolean',
        'expires_at' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'actual_checkout_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function sessionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(CustomerSession::class, 'host_session_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CustomerSession::class, 'host_session_id');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
