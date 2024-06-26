<?php

namespace App\Console;

use App\Models\AssessmentCourse;
use App\Models\Settings;
use App\Services\CanvasService;
use App\Services\SeedService;
use App\Services\SyncService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            Log::info('Schedule: attempted run');
        })->everyMinute();

        $schedule->call(function () {
            Log::info('Schedule: Backing up database');
            SeedService::backupDatabase();
            self::updateScheduleRunAt();
        })->hourly();

        $schedule->call(function () {
            Log::info('Schedule: Syncing with Canvas');
            SyncService::sync();
            self::updateScheduleRunAt();
        })->everyThirtyMinutes();

        $schedule->call(function () {
            Log::info('Schedule: Posting final grades');
            $this->postFinalGrades();
            self::updateScheduleRunAt();
        })->dailyAt('01:00');

    }

    /**
     * @throws Exception
     */
    public function postFinalGrades(): void
    {
        $assessmentCourses = AssessmentCourse::all();

        foreach ($assessmentCourses as $assessmentCourse) {
            if (! $assessmentCourse->assessment_canvas_id || ! $assessmentCourse->course->master_id) {
                continue;
            }

            if ($assessmentCourse->due_at === null) {
                continue;
            }

            if ($assessmentCourse->is_active === false) {
                continue;
            }

            $dueAt = Carbon::parse($assessmentCourse->due_at);

            // if it was due within the last day, post the final grade
            if ($dueAt->isPast() && $dueAt->diffInDays(Carbon::now()) < 1.01) {
                CanvasService::regradeAssessmentCourse($assessmentCourse);
            }
        }
    }

    public static function updateScheduleRunAt(): void
    {
        $settings = Settings::first();
        $settings->last_schedule_run_at = Carbon::now();
        $settings->save();
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
