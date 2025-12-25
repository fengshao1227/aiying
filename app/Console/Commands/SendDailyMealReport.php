<?php

namespace App\Console\Commands;

use App\Services\V2\WeworkNotifyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyMealReport extends Command
{
    protected $signature = 'daily-meal-report:send {--date= : The date to report (Y-m-d), defaults to tomorrow}';

    protected $description = 'Send daily meal order statistics notification to WeChat Work';

    public function handle(WeworkNotifyService $notifyService): int
    {
        $dateInput = $this->option('date');

        if ($dateInput) {
            $parsed = Carbon::createFromFormat('Y-m-d', $dateInput);
            if (!$parsed || $parsed->format('Y-m-d') !== $dateInput) {
                $this->error("Invalid date format: {$dateInput}. Expected Y-m-d");
                Log::warning('Daily meal report invalid date format', ['input' => $dateInput]);
                return self::FAILURE;
            }
            $date = $dateInput;
        } else {
            $date = now()->addDay()->toDateString();
        }

        $this->info("Sending daily meal report for date: {$date}");
        Log::info('Daily meal report command started', ['date' => $date]);

        try {
            $success = $notifyService->sendDailyMealReport($date);

            if (!$success) {
                $this->error('Daily meal report send failed.');
                Log::warning('Daily meal report send failed', ['date' => $date]);
                return self::FAILURE;
            }

            $this->info('Daily meal report sent successfully.');
            Log::info('Daily meal report sent successfully', ['date' => $date]);
            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Daily meal report command exception', [
                'date' => $date,
                'exception' => $e,
            ]);
            return self::FAILURE;
        }
    }
}
