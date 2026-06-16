<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperOrderPromotion
 */
class OrderPromotion extends Model
{
    protected $table = 'order_promotions';

    public $timestamps = false; // created_at timestamp set on insertion, no updated_at

    protected $fillable = [
        'order_id',
        'promotion_id',
        'discount_amount',
        'created_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
