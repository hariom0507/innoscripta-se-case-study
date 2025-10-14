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
        // Run on every hour
        // ->withoutOverlapping(); is to avoid multiple command to run at the same time we can remove or keep this.
        $schedule->command('articles:fetch')->hourly()->withoutOverlapping();
        // Run once every day at 00:00
        $schedule->command('articles:fetch')->dailyAt('00:00')->withoutOverlapping();;
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
