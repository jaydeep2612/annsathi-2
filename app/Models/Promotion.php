<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @mixin IdeHelperPromotion
 */
class Promotion extends Model
{
    use BelongsToRestaurant, SoftDeletes, LogsActivity;

    protected $fillable = [
        'restaurant_id',
        'name',
        'code',
        'type', // flat, percent, bogo
        'value',
        'min_order_amount',
        'max_discount_amount',
        'bogo_buy_menu_item_id',
        'bogo_get_menu_item_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function bogoBuyItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'bogo_buy_menu_item_id');
    }

    public function bogoGetItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'bogo_get_menu_item_id');
    }

    public function orderPromotions(): HasMany
    {
        return $this->hasMany(OrderPromotion::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
