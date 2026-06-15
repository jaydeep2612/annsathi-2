<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use App\Models\Traits\ImmutableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperCreditNote
 */
class CreditNote extends Model
{
    use BelongsToRestaurant, HasBranch, ImmutableModel;

    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'invoice_id',
        'order_id',
        'credit_note_number',
        'reason',
        'amount',
        'issued_by',
        'approved_by',
        'items_snapshot',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'items_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
