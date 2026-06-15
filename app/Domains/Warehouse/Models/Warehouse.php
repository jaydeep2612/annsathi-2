<?php

declare(strict_types=1);

namespace App\Domains\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }
}
