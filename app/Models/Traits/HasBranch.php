<?php

namespace App\Models\Traits;

use App\Models\Scopes\BranchScope;

trait HasBranch
{
    /**
     * Boot the trait.
     */
    public static function bootHasBranch(): void
    {
        static::addGlobalScope(new BranchScope);

        static::creating(function ($model) {
            if (app()->bound('tenant.branch_id') && ! $model->branch_id) {
                $model->branch_id = app('tenant.branch_id');
            }
        });
    }

    /**
     * Get the branch that owns the model.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }
}
