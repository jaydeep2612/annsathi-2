<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;

class OfflineAction extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $table = 'offline_actions';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'device_identifier',
        'action_type',
        'payload',
        'status',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'synced_at' => 'datetime',
    ];
}
