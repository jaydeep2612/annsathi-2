<?php

declare(strict_types=1);

namespace App\Domains\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseMovement extends Model
{
    use BelongsToRestaurant;

    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'from_warehouse_id',
        'to_branch_id',
        'grocery_item_id',
        'quantity',
        'transfer_type',
        'recorded_by',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'float',
        'created_at' => 'datetime',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class, 'to_branch_id');
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(\App\Models\GroceryItem::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }
}
