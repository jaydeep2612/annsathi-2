<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperRestaurant
 */
class Restaurant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'address',
        'phone_no',
        'gst_no',
        'upi_id',
        'subscription_plan',
        'features',
        'settings',
        'user_limits',
        'table_limits',
        'rooms_limit',
        'max_branches',
        'is_active',
        'trial_ends_at',
        'created_by',
    ];

    protected $casts = [
        'features' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper to retrieve a feature flag value.
     */
    public function feature(string $key, mixed $default = null): mixed
    {
        return data_get($this->features, $key, $default);
    }

    /**
     * Helper to retrieve a setting value.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
