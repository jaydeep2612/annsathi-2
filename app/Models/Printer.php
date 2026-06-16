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
 * @mixin IdeHelperPrinter
 */
class Printer extends Model
{
    use BelongsToRestaurant, HasBranch, LogsActivity;

    protected $table = 'printers';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'name',
        'connection_type',
        'ip_address',
        'port',
        'mac_address',
        'printer_model',
        'is_active',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
    ];

    public function printerGroups(): BelongsToMany
    {
        return $this->belongsToMany(PrinterGroup::class, 'printer_group_printers', 'printer_id', 'printer_group_id');
    }

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
