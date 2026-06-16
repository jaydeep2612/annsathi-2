<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncQueue extends Model
{
    use HasBranch;

    protected $table = 'sync_queue';

    protected $fillable = [
        'branch_id',
        'action',
        'payload',
        'attempts',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
    ];
}
