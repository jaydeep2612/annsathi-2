<?php

declare(strict_types=1);

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageSnapshot extends Model
{
    protected $table = 'usage_snapshots';

    protected $fillable = [
        'restaurant_id',
        'snapshot_date',
        'branches_count',
        'users_count',
        'orders_count',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'branches_count' => 'integer',
        'users_count' => 'integer',
        'orders_count' => 'integer',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Restaurant::class);
    }
}
