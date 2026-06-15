<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\ImmutableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAuditLog
 */
class AuditLog extends Model
{
    use BelongsToRestaurant, ImmutableModel;

    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'event',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
