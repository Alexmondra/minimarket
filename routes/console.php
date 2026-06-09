<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:procesar-lotes-vencidos')->daily();
Schedule::command('backup:database-local')->dailyAt(config('backup.schedule.local_database_at'));
Schedule::command('backup:run')->dailyAt(config('backup.schedule.run_at'));
Schedule::command('backup:clean')->dailyAt(config('backup.schedule.clean_at'));
Schedule::command('backup:monitor')->dailyAt(config('backup.schedule.monitor_at'));
