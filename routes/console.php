<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ✅ Auto-generate class sessions daily from routine entries
// Generates sessions 8 weeks into the future (skips existing + holidays)
Schedule::command('sessions:generate --weeks=8')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground();

// ✅ 3-Minute Fail-Safe Payment Auto-Reconciler for network drops
Schedule::command('payment:reconcile-pending --minutes=3')
    ->everyThreeMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// ✅ Daily Morning Class Reminder to students via Email & Notification at 07:00 AM
Schedule::command('classes:send-daily-reminders')
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->runInBackground();


