<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\AssessmentCourse;
use App\Models\Master;

new class extends Component {
    public Collection $assessments;
}; ?>

<div class="space-y-4">
    @if ($assessments->isNotEmpty())
        @foreach ($assessments as $assessment)
            @if ($assessment->pivot->assessment_canvas_id != -1)
                <livewire:assessment.assessment-card :assessment="$assessment" :courseId="$assessment->pivot->course_id" :assessmentCanvasId="$assessment->pivot->assessment_canvas_id"
                    :dueAt="$assessment->pivot->due_at" :key="$assessment->id" />
            @endif
        @endforeach
    @else
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="flex max-h-20 w-full items-center justify-between px-6 py-4 text-gray-900">
                No assessments found
            </div>
        </div>
    @endif
</div>
