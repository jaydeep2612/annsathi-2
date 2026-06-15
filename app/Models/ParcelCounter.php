<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin IdeHelperParcelCounter
 */
class ParcelCounter extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'name',
        'qr_token',
        'qr_image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sessions(): MorphMany
    {
        return $this->morphMany(CustomerSession::class, 'sessionable');
    }
}
