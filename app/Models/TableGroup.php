<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperTableGroup
 */
class TableGroup extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'name',
        'merged_by',
        'customer_session_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function merger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by');
    }

    public function customerSession(): BelongsTo
    {
        return $this->belongsTo(CustomerSession::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class);
    }
}
