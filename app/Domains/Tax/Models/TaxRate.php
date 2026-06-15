<?php

declare(strict_types=1);

namespace App\Domains\Tax\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaxRate extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'rate',
        'type',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * The groups associated with the tax rate.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(TaxGroup::class, 'tax_rules', 'tax_rate_id', 'tax_group_id')
            ->withTimestamps();
    }
}
