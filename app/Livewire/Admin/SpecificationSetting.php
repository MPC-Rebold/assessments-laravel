<?php

namespace App\Livewire\Admin;

use App\Models\AssessmentCourse;
use App\Models\Course;
use App\Services\CanvasService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class SpecificationSetting extends Component
{
    use Actions;

    public Course $course;

    public bool $specification_grading;

    public string $specification_grading_threshold;

    public bool $modalOpen = false;

    public function mount(Course $course): void
    {
        $this->course = $course;

        $this->specification_grading = $this->course->specification_grading;

        if ($this->specification_grading) {
            $this->specification_grading_threshold = $this->course->specification_grading_threshold * 100 . '%';
        } else {
            $this->specification_grading_threshold = 'OFF';
        }
    }

    public function openModal(): void
    {
        $this->modalOpen = true;
    }

    public function updateSpecificationGrading(): void
    {
        $specification_grading = $this->specification_grading_threshold !== 'OFF';

        if ($specification_grading) {
            $specification_grading_threshold = (int) $this->specification_grading_threshold / 100;
        } else {
            $specification_grading_threshold = -1;
        }

        DB::beginTransaction();
        try {
            $this->course->update([
                'specification_grading' => $specification_grading,
                'specification_grading_threshold' => $specification_grading_threshold,
            ]);

            $this->regradeAssessments();

            $this->specification_grading = $this->course->specification_grading;

        } catch (Exception $e) {
            DB::rollBack();
            $this->notification()->error(
                'Failed to update specification grading with error ' . $e->getMessage(),
            );

            return;
        }

        $this->notification()->success(
            'Specification Grading Turned ' . ($specification_grading ? 'On' : 'Off'),
        );

        DB::commit();

        $this->modalOpen = false;
    }

    public function regradeAssessments(): void
    {
        $assessmentCourses = AssessmentCourse::where('course_id', $this->course->id)->get();

        foreach ($assessmentCourses as $assessmentCourse) {
            if (! $assessmentCourse->assessment_canvas_id || ! $assessmentCourse->course->master_id) {
                continue;
            }

            if ($assessmentCourse->due_at && Carbon::parse($assessmentCourse->due_at)->isPast()) {
                continue;
            }

            $this->regradeAssessmentCourse($assessmentCourse, false);
        }
    }

    public function regradeAssessmentCourse(AssessmentCourse $assessmentCourse, bool $regradePastDue = true): void
    {
        if ($assessmentCourse->due_at && Carbon::parse($assessmentCourse->due_at)->isPast() && ! $regradePastDue) {
            return;
        }

        CanvasService::setMaxPoints($assessmentCourse);
        $gradeAssessmentResponse = CanvasService::gradeAssessment($assessmentCourse);

        if ($gradeAssessmentResponse->status() !== 200) {
            $this->notification()->warning(
                'Failed to regrade assessment ' . $assessmentCourse->assessment->title,
                'Status: ' . $gradeAssessmentResponse->status()
            );
        }
    }

    public function render(): View
    {
        return view('livewire.admin.specification-setting');
    }
}
