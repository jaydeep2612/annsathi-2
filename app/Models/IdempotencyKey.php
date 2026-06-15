<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;

/**
 * @mixin IdeHelperIdempotencyKey
 */
class IdempotencyKey extends Model
{
    use BelongsToRestaurant;

    protected $table = 'idempotency_keys';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'restaurant_id',
        'scope',
        'status',
        'reference_id',
        'response',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'response' => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
