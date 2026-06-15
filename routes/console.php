<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\OrderCleanupJob;
use App\Jobs\SessionExpiryJob;
use App\Jobs\SummaryComputationJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register Background Scheduled Jobs
Schedule::job(new OrderCleanupJob)->hourly();
Schedule::job(new SessionExpiryJob)->everyFiveMinutes();
Schedule::job(new SummaryComputationJob)->dailyAt('00:05');

// Spatie Backups schedules
Schedule::command('backup:clean')->dailyAt('01:00');
Schedule::command('backup:run')->dailyAt('02:00');
