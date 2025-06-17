<?php

use Carbon\Carbon;
use Livewire\Volt\Component;
use App\Models\Settings;

new class extends Component {
    public bool $scheduleIsRunning;

    public function mount(): void
    {
        $scheduleLastRun = Settings::first()->last_schedule_run_at;
        // the schedule has been run in the last 12 hours or is null
        $this->scheduleIsRunning = $scheduleLastRun === null || Carbon::parse($scheduleLastRun)->diffInHours(Carbon::now()) < 12;
    }
}; ?>

<div>
    @if (!$scheduleIsRunning)
        <div class='rounded-lg border border-warning-600 bg-warning-50 p-4'>
            <div class="flex items-center border-b-2 border-warning-200 pb-3">
                <x-icon name="exclamation" class="h-6 w-6 text-warning-700" />
                <div class="ml-1 text-lg text-warning-700">
                    <b>Auto Syncing disabled</b>
                </div>
            </div>
            <div class="ml-5 mt-2 pl-1">
                <p class="text-warning-700">
                    Background tasks are not enabled. The following will have not run:
                </p>
                <div class="ml-5 mt-2 flex items-center justify-between pl-1">
                    <ul class="list-disc space-y-1 text-warning-700">
                        <li>
                            Automatic submission of student grades to Canvas on assessment deadline
                        </li>
                        <li>
                            Auto Syncing of Canvas
                        </li>
                        <li>
                            Database backups
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
