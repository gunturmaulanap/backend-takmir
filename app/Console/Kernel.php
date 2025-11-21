<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up expired refresh tokens daily at 2 AM
        $schedule->command('app:cleanup-refresh-tokens')
            ->dailyAt('02:00')
            ->description('Clean up expired and revoked refresh tokens');

        // Optional: Run cleanup every 6 hours for high-traffic apps
        // $schedule->command('app:cleanup-refresh-tokens')
        //     ->cron('0 */6 * * *')
        //     ->description('Clean up expired and revoked refresh tokens');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}