<?php

declare(strict_types=1);

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageMetric extends Model
{
    protected $table = 'usage_metrics';

    protected $fillable = [
        'restaurant_id',
        'metric_key',
        'usage_count',
        'reset_at',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'reset_at' => 'datetime',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Restaurant::class);
    }
}
