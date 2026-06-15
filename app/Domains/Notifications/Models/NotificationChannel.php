<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationChannel extends Model
{
    protected $fillable = [
        'name',
        'driver',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
}
