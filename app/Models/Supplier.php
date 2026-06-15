<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperSupplier
 */
class Supplier extends Model
{
    use BelongsToRestaurant, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'gst_number',
        'payment_terms',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function groceryItems(): HasMany
    {
        return $this->hasMany(GroceryItem::class);
    }
}
