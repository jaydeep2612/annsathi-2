<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPayment
 */
class Payment extends Model
{
    use BelongsToRestaurant, HasBranch;

    /**
     * Enforce settlement immutability rules.
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($payment) {
            if ($payment->getOriginal('status') === 'paid' && !in_array($payment->status, ['refunded', 'partial'])) {
                throw new \Exception("Settled payment cannot be modified.");
            }
        });

        static::deleting(function ($payment) {
            if ($payment->getOriginal('status') === 'paid') {
                throw new \Exception("Settled payment cannot be deleted.");
            }
        });

        static::saved(function ($payment) {
            if ($payment->order_id) {
                $order = $payment->order;
                if ($order) {
                    $totalPaid = $order->payments()
                        ->where('status', 'paid')
                        ->sum('amount');
                    
                    if ($totalPaid >= $order->total_amount) {
                        $order->update(['payment_status' => 'paid']);
                    } elseif ($totalPaid > 0) {
                        $order->update(['payment_status' => 'partially_paid']);
                    } else {
                        $hasRefunds = $order->payments()
                            ->where('status', 'refunded')
                            ->exists();
                        if ($hasRefunds) {
                            $order->update(['payment_status' => 'refunded']);
                        } else {
                            $order->update(['payment_status' => 'unpaid']);
                        }
                    }
                }
            }
        });
    }

    protected $fillable = [
        'order_id',
        'bill_group_id',
        'restaurant_id',
        'branch_id',
        'shift_id',
        'payment_method',
        'amount',
        'reference_note',
        'received_by',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function billGroup(): BelongsTo
    {
        return $this->belongsTo(BillGroup::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
