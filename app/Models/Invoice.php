<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use App\Models\Traits\ImmutableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperInvoice
 */
class Invoice extends Model
{
    use BelongsToRestaurant, HasBranch, ImmutableModel;

    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'order_id',
        'payment_id',
        'customer_session_id',
        'shift_id',
        'invoice_number',
        'invoice_prefix',
        'invoice_sequence',
        'invoice_date',
        'gstin',
        'place_of_supply',
        'customer_name',
        'subtotal',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'extra_charges',
        'extra_charges_label',
        'grand_total',
        'items_snapshot',
        'voided_by_credit_note_id',
        'created_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'extra_charges' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'items_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function customerSession(): BelongsTo
    {
        return $this->belongsTo(CustomerSession::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class, 'voided_by_credit_note_id');
    }
}
