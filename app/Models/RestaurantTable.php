<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin IdeHelperRestaurantTable
 */
class RestaurantTable extends Model
{
    use BelongsToRestaurant, HasBranch, SoftDeletes;

    protected $table = 'restaurant_tables';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'name',
        'capacity',
        'qr_token',
        'qr_image_path',
        'status',
        'table_group_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($table) {
            if (empty($table->qr_token)) {
                $table->qr_token = 'TBL-' . strtoupper(\Illuminate\Support\Str::random(12));
            }
        });
    }

    public function tableGroup(): BelongsTo
    {
        return $this->belongsTo(TableGroup::class);
    }

    public function sessions(): MorphMany
    {
        return $this->morphMany(CustomerSession::class, 'sessionable');
    }
}
