<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @mixin IdeHelperReservation
 */
class Reservation extends Model
{
    use BelongsToRestaurant, HasBranch, LogsActivity;

    protected $table = 'reservations';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'restaurant_table_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'reservation_time',
        'duration_minutes',
        'pax_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'reservation_time' => 'datetime',
        'duration_minutes' => 'integer',
        'pax_count' => 'integer',
    ];

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
