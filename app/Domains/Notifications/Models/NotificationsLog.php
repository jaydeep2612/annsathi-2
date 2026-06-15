<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NotificationsLog extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $table = 'notifications_log';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
