<?php

declare(strict_types=1);

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SettingGroup extends Model
{
    use LogsActivity;

    protected $table = 'setting_groups';

    protected $fillable = [
        'key',
        'display_name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
