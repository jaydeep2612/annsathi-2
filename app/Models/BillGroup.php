<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperBillGroup
 */
class BillGroup extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'customer_session_id',
        'name',
        'target_amount',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
    ];

    public function customerSession(): BelongsTo
    {
        return $this->belongsTo(CustomerSession::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillGroupItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
