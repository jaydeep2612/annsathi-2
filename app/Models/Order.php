<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperOrder
 */
class Order extends Model
{
    use BelongsToRestaurant, HasBranch;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'customer_session_id',
        'service_type',
        'status',
        'payment_status',
        'parent_order_id',
        'is_merged',
        'bill_group_id',
        'assigned_waiter_id',
        'created_by',
        'customer_name',
        'notes',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'extra_charges',
        'extra_charges_label',
        'total_amount',
        'confirmed_at',
        'prepared_at',
        'served_at',
        'completed_at',
        'cancelled_at',
        'shift_id',
    ];

    protected $casts = [
        'is_merged' => 'boolean',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'extra_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'prepared_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customerSession(): BelongsTo
    {
        return $this->belongsTo(CustomerSession::class);
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function childOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_order_id');
    }

    public function billGroup(): BelongsTo
    {
        return $this->belongsTo(BillGroup::class);
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_waiter_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
