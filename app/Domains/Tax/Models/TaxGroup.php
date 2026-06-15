<?php

declare(strict_types=1);

namespace App\Domains\Tax\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaxGroup extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The rates associated with the tax group.
     */
    public function rates(): BelongsToMany
    {
        return $this->belongsToMany(TaxRate::class, 'tax_rules', 'tax_group_id', 'tax_rate_id')
            ->withTimestamps();
    }
}
