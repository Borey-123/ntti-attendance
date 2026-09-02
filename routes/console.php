<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// System maintenance
Schedule::command('system:cleanup')->daily();
Schedule::command('attendance:auto-checkout')->hourly();

// Telegram automated notifications & reports
Schedule::command('telegram:monthly-report')->monthlyOn(1, '08:00');
Schedule::command('telegram:morning-reminder')->dailyAt('07:30');
Schedule::command('telegram:absent-alert')->dailyAt('18:45');
Schedule::command('telegram:late-warning')->weeklyOn(5, '17:00');
