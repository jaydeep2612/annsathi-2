<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperRecipeVersion
 */
class RecipeVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'menu_item_id',
        'recipe_id',
        'snapshot',
        'changed_by',
        'change_reason',
        'effective_from',
        'effective_until',
        'created_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'created_at' => 'datetime',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
