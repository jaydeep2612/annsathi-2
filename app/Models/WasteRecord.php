<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperWasteRecord
 */
class WasteRecord extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $table = 'waste_records';

    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'grocery_item_id',
        'measurement_unit_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'reason',
        'reason_notes',
        'recorded_by',
        'shift_id',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }

    public function measurementUnit(): BelongsTo
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
