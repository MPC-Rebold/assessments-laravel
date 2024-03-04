<?php

namespace App\Console;

use App\Livewire\Admin\SpecificationSetting;
use App\Livewire\Admin\Sync;
use App\Models\AssessmentCourse;
use App\Services\SeedService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;
use function Laravel\Prompts\error;

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
            $this->postFinalGrades();
        })->dailyAt('01:00');

    }

    public function postFinalGrades(): void
    {
        $specificationSetting = new SpecificationSetting();
        $assessmentCourses = AssessmentCourse::all();

        foreach ($assessmentCourses as $assessmentCourse) {
            if (! $assessmentCourse->assessment_canvas_id || ! $assessmentCourse->course->master_id) {
                continue;
            }

            if ($assessmentCourse->due_at === null) {
                continue;
            }

            $dueAt = Carbon::parse($assessmentCourse->due_at);

            // if it was due within the last day, post the final grade
            if ($dueAt->isPast() && $dueAt->diffInDays(Carbon::now()) < 1.005) {
                $specificationSetting->regradeAssessmentCourse($assessmentCourse);
            }
        }
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
