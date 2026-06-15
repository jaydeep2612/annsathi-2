<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin IdeHelperRoom
 */
class Room extends Model
{
    use BelongsToRestaurant, HasBranch, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'name',
        'room_number',
        'floor',
        'capacity',
        'rate_per_night',
        'qr_token',
        'qr_image_path',
        'status',
        'is_active',
    ];

    protected $casts = [
        'floor' => 'integer',
        'capacity' => 'integer',
        'rate_per_night' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function sessions(): MorphMany
    {
        return $this->morphMany(CustomerSession::class, 'sessionable');
    }
}
