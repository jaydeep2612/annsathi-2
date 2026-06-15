<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperBranch
 */
class Branch extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'address',
        'phone_no',
        'upi_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branches');
    }
}
