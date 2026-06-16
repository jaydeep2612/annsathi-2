<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @mixin IdeHelperPrinterGroup
 */
class PrinterGroup extends Model
{
    use BelongsToRestaurant, HasBranch, LogsActivity;

    protected $table = 'printer_groups';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'name',
        'description',
    ];

    public function printers(): BelongsToMany
    {
        return $this->belongsToMany(Printer::class, 'printer_group_printers', 'printer_group_id', 'printer_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(PrinterRoute::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
