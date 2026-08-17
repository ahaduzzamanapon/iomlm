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
