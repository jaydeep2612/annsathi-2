<?php

declare(strict_types=1);

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    protected $table = 'plan_features';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'plan_key',
        'feature_flag_id',
    ];

    public function featureFlag(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class);
    }
}
