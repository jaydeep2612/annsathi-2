<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @mixin IdeHelperPrintJob
 */
class PrintJob extends Model
{
    use BelongsToRestaurant, HasBranch, LogsActivity;

    protected $table = 'print_jobs';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'printer_id',
        'title',
        'content',
        'status',
        'error_message',
        'attempts',
    ];

    protected $casts = [
        'attempts' => 'integer',
    ];

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class, 'printer_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
