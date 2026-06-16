<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperLoyaltyTransaction
 */
class LoyaltyTransaction extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'order_id',
        'type', // earn, redeem, adjustment
        'points',
        'notes',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
