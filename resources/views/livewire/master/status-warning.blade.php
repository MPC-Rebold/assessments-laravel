<?php

use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Course;

new class extends Component {
    public Collection $missingAssessments;
    public Collection $missingCourses;
}; ?>

<div class='border border-warning-600 bg-warning-50 p-4 sm:rounded-lg'>
    <div class="flex items-center border-b-2 border-warning-200 pb-3">
        <x-icon name="exclamation" class="h-6 w-6 text-warning-600" />
        <span class="ml-1 text-lg font-semibold text-warning-600">
            Warning
        </span>
    </div>
    <div class="ml-5 mt-2 pl-1">
        <ul class="list-disc space-y-1 text-warning-600">
            @foreach ($missingCourses as $course)
                <li>
                    <div class="flex overflow-auto text-nowrap">
                        <p>The course&nbsp;</p>
                        <p class="font-bold">{{ $course->title }}</p>
                        <p>&nbsp;was not found in Canvas. Try disconnecting it.</p>
                    </div>
                </li>
            @endforeach
            @foreach ($missingAssessments as $assessment)
                <li>
                    <div class="flex overflow-auto text-nowrap">
                        <p>The assessment&nbsp;</p>
                        <p class="font-bold">{{ $assessment->title }}</p>
                        <p>&nbsp;of course&nbsp;</p>
                        <p class="font-bold">{{ Course::find($assessment->pivot->course_id)->title }}</p>
                        <p>&nbsp;was not found in Canvas.</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
