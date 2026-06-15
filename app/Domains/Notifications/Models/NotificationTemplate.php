<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;

class NotificationTemplate extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'event_name',
        'title',
        'body',
        'channels',
        'is_active',
    ];

    protected $casts = [
        'channels' => 'array',
        'is_active' => 'boolean',
    ];
}
