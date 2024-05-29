<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use App\Models\Course;
use App\Models\Assessment;

new class extends Component {
    public Collection $missingAssessments;
    public Collection $missingCourses;

    public function mount(Collection $missingCourses, Collection $missingAssessments): void
    {
        $this->missingCourses = $missingCourses->sortBy('title');
        $this->missingAssessments = $missingAssessments->sortBy('pivot.course_id');
    }

    #[On('refreshConnectedCourses')]
    public function refreshConnectedCourses(array $missingCourses, array $missingAssessments): void
    {
        $this->mount(Course::hydrate($missingCourses), Assessment::hydrate($missingAssessments));
    }
}; ?>

<div class='border border-warning-500 bg-warning-50 p-4 text-warning-800 sm:rounded-lg'>
    <div class="flex items-center border-b-2 border-warning-200 pb-3">
        <x-icon name="exclamation" class="h-6 w-6" />
        <span class="ml-1 text-lg font-semibold">
            Warning
        </span>
    </div>
    <div class="ml-5 mt-2 pl-1">
        <ul class="list-disc space-y-1">
            @foreach ($missingCourses as $course)
                <li>
                    <div>
                        The course <b>{{ $course->title }}</b>
                        was not found in Canvas. Try disconnecting it.
                    </div>
                </li>
            @endforeach
            @foreach ($missingAssessments as $assessment)
                <li>
                    <div>
                        The assessment <b>{{ $assessment->title }}</b> of course
                        <b>{{ Course::find($assessment->pivot->course_id ?? $assessment->pivot['course_id'])->title }}</b>
                        was not found in Canvas. It will not be available to
                        students.
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
