<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Makes a backup of the current sqlite database from /database/database.sqlite to /storage/backups
     * Deletes the oldest backup if the total size of all backups exceeds 500MB.
     *
     * @return void
     */
    public function backupDatabase(): void
    {
        $backupPath = storage_path('backups');

        if (! is_dir($backupPath)) {
            mkdir($backupPath);
        }

        $totalBackupSize = 0;
        $files = glob($backupPath . '/*.sqlite');
        foreach ($files as $file) {
            $totalBackupSize += filesize($file);
        }

        $maxSize = 500000000;

        if ($totalBackupSize > $maxSize && count($files) > 0) {
            unlink($files[0]);
        }

        $backupName = date('Y-m-d-H-i-s') . '.sqlite';
        $backupFile = $backupPath . '/' . $backupName;

        copy(database_path('database.sqlite'), $backupFile);
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $this->backupDatabase();
        })->everyTenMinutes();
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
