<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperBranchMenuItem
 */
class BranchMenuItem extends Model
{
    use HasBranch;

    protected $fillable = [
        'branch_id',
        'menu_item_id',
        'is_available',
        'override_price',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'override_price' => 'decimal:2',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
