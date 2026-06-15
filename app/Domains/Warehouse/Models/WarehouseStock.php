<?php

declare(strict_types=1);

namespace App\Domains\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStock extends Model
{
    protected $table = 'warehouse_stock';

    protected $fillable = [
        'warehouse_id',
        'grocery_item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(\App\Models\GroceryItem::class);
    }
}
