<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperDailySalesSummary
 */
class DailySalesSummary extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $table = 'daily_sales_summaries';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'summary_date',
        'shift_id',
        'total_orders',
        'completed_orders',
        'cancelled_orders',
        'gross_revenue',
        'discount_total',
        'tax_total',
        'extra_charges_total',
        'net_revenue',
        'cash_collected',
        'upi_collected',
        'card_collected',
        'complimentary_total',
        'avg_order_value',
        'peak_hour',
        'dine_in_orders',
        'room_service_orders',
        'parcel_orders',
        'manual_orders',
        'computed_at',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'gross_revenue' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'extra_charges_total' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'cash_collected' => 'decimal:2',
        'upi_collected' => 'decimal:2',
        'card_collected' => 'decimal:2',
        'complimentary_total' => 'decimal:2',
        'avg_order_value' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
