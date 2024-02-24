<?php

namespace App\Console;

use App\Livewire\Admin\Sync;
use App\Services\SeedService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            SeedService::backupDatabase();
        })->everyFifteenMinutes();

        $schedule->call(function () {
            $sync = new Sync();
            $sync->sync();
        })->everyFiveMinutes();

        $schedule->call(function () {
            //
        })->dailyAt();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
