<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NotificationPreference extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'event_name',
        'channels',
    ];

    protected $casts = [
        'channels' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
