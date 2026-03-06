<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('adopt:send-reminders')->daily()->at('09:00');
Schedule::command('backup:database')->hourly()->when(function () {
    $interval = (int) \App\Models\Setting::get('backup_interval_hours', 4);
    return $interval > 0 && now()->hour % $interval === 0;
});
