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
 * @mixin IdeHelperPrinterRoute
 */
class PrinterRoute extends Model
{
    use BelongsToRestaurant, HasBranch, LogsActivity;

    protected $table = 'printer_routes';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'kitchen_station_id',
        'category_id',
        'printer_group_id',
        'route_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kitchenStation(): BelongsTo
    {
        return $this->belongsTo(KitchenStation::class, 'kitchen_station_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function printerGroup(): BelongsTo
    {
        return $this->belongsTo(PrinterGroup::class, 'printer_group_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
