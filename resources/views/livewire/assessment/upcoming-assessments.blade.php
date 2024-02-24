<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use App\Models\Course;
use App\Models\AssessmentCourse;
use Carbon\Carbon;

new class extends Component {
    public int $courseId;

    public Collection $assessments;

    public function mount(): void
    {
        if (isset($this->courseId)) {
            $assessments = auth()
                ->user()
                ->courses->find($this->courseId)->assessments;
            $this->assessments = $assessments
                ->filter(function ($assessment) {
                    return $assessment->pivot->due_at === null || Carbon::parse($assessment->pivot->due_at)->isFuture();
                })
                ->sortBy('pivot.due_at');
        } else {
            $assessments = auth()->user()->courses->map->assessments->flatten();

            $this->assessments = $assessments
                ->filter(function ($assessment) {
                    return $assessment->pivot->due_at === null || Carbon::parse($assessment->pivot->due_at)->isFuture();
                })
                ->flatten()
                ->sortBy('pivot.due_at');
        }

        $this->assessments = $this->assessments->take(4);
    }
}; ?>

<livewire:assessment.assessment-cards :assessments="$assessments" />
