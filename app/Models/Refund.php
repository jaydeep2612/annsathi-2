<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\ImmutableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperRefund
 */
class Refund extends Model
{
    use BelongsToRestaurant, ImmutableModel;

    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'restaurant_id',
        'credit_note_id',
        'amount',
        'reason',
        'refund_method',
        'processed_by',
        'approval_request_id',
        'processed_at',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }
}
