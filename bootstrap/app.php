<?php

use App\Models\V2\SystemConfig;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('v2')
                ->middleware('api')
                ->group(base_path('routes/v2.php'));
        },
    )
    ->withSchedule(function (Schedule $schedule): void {
        $time = '20:00';

        try {
            $configuredTime = SystemConfig::getDailyReportTime();
            if (is_string($configuredTime) && preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', trim($configuredTime))) {
                $time = trim($configuredTime);
            } elseif ($configuredTime) {
                Log::warning('Daily meal report schedule time invalid, using default', ['configured' => $configuredTime]);
            }
        } catch (\Throwable $e) {
            Log::error('Daily meal report schedule config load failed', ['error' => $e->getMessage()]);
        }

        $schedule->command('daily-meal-report:send')
            ->dailyAt($time)
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        $schedule->command('orders:cancel-expired')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuthenticate::class,
            'v2.user.auth' => \App\Http\Middleware\V2UserAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
