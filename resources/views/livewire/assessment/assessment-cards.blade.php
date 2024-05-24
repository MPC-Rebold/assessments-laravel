<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\AssessmentCourse;
use App\Models\Master;

new class extends Component {
    public Collection $assessmentCourses;
    public Collection $activeAssessmentCourses;
    public Collection $inactiveAssessmentCourses;

    public function mount(Collection $assessmentCourses): void
    {
        $validAssessmentCourses = $assessmentCourses->where('assessment_canvas_id', '!=', -1);
        $this->assessmentCourses = $validAssessmentCourses->whereNotNull('due_at')->sortBy('due_at')->concat($validAssessmentCourses->whereNull('due_at')->sortBy('title'));

        $this->activeAssessmentCourses = $this->assessmentCourses->where('is_active', 1);
        $this->inactiveAssessmentCourses = $this->assessmentCourses->where('is_active', 0);
    }
}; ?>

<div class="space-y-4">
    @if ($assessmentCourses->isNotEmpty())
        @foreach ($activeAssessmentCourses as $assessmentCourse)
            <livewire:assessment.assessment-card-active :assessmentCourse="$assessmentCourse" :key="$assessmentCourse->id" />
        @endforeach
        @if ($inactiveAssessmentCourses->isNotEmpty())
            <div x-data="{ open: false }">
                <button class="group w-full bg-gray-200 p-4 shadow sm:rounded-lg sm:px-6" @click="open = !open">
                    <div class="flex items-center justify-between">
                        <div class="text-md">
                            Show locked assessments ({{ $inactiveAssessmentCourses->count() }})
                        </div>
                        <div class="flex items-center space-x-2">
                            <div :class="{ 'rotate-180': open }" class="transition-transform ease-in-out">
                                <x-icon name="chevron-down"
                                    class="h-5 w-5 transition-all ease-in-out group-hover:scale-125" />
                            </div>
                        </div>
                    </div>
                </button>
                <div :class="{ 'max-h-0 invisible': !open, 'max-h-[999vh] py-4': open }"
                    class="overflow-hidden transition-all duration-300 ease-in-out">
                    <div class="space-y-4">
                        @foreach ($inactiveAssessmentCourses as $assessmentCourse)
                            <livewire:assessment.assessment-card-inactive :assessmentCourse="$assessmentCourse" :key="$assessmentCourse->id" />
                        @endforeach
                    </div>
                </div>
            </div>

        @endif
    @else
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="flex max-h-20 w-full items-center justify-between px-6 py-4 text-gray-900">
                No Assessments
            </div>
        </div>
    @endif
</div>
