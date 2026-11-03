<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->isSecure()
            || request()->header('X-Forwarded-Proto') === 'https'
            || request()->server('HTTP_X_FORWARDED_PROTO') === 'https'
            || str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.custom');

        // Failsafe: Ensure morning class reminders trigger automatically every morning at or after 07:00 AM (Asia/Dhaka)
        if (!app()->runningInConsole()) {
            app()->terminating(function () {
                try {
                    $nowBd = \Carbon\Carbon::now('Asia/Dhaka');
                    if ($nowBd->hour >= 7) {
                        $todayKey = 'daily_class_reminders_triggered_' . $nowBd->toDateString();
                        if (!\Illuminate\Support\Facades\Cache::has($todayKey)) {
                            if (\Illuminate\Support\Facades\Cache::add($todayKey . '_lock', 1, 600)) {
                                \Illuminate\Support\Facades\Artisan::call('classes:send-daily-reminders');
                                \Illuminate\Support\Facades\Cache::put($todayKey, now()->toDateTimeString(), 86400);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Failsafe daily class reminder exception: ' . $e->getMessage());
                }
            });
        }
    }
}
