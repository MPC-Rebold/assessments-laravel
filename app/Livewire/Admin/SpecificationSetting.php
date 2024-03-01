<?php

namespace App\Livewire\Admin;

use App\Models\AssessmentCourse;
use App\Models\Settings;
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

    public bool $specification_grading;

    public string $specification_grading_threshold;

    public bool $modalOpen = false;

    public function openModal(): void
    {
        $this->modalOpen = true;
    }

    public function mount(): void
    {
        $this->specification_grading = Settings::sole()->specification_grading;

        if ($this->specification_grading) {
            $this->specification_grading_threshold = Settings::sole()->specification_grading_threshold * 100 . '%';
        } else {
            $this->specification_grading_threshold = 'OFF';
        }
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
            Settings::sole()->update([
                'specification_grading' => $specification_grading,
                'specification_grading_threshold' => $specification_grading_threshold,
            ]);

            $this->regradeAssessments();

            $this->specification_grading = Settings::sole()->specification_grading;

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
        $assessmentCourses = AssessmentCourse::all();

        foreach ($assessmentCourses as $assessmentCourse) {
            if (! $assessmentCourse->assessment_canvas_id || ! $assessmentCourse->course->master_id) {
                continue;
            }

            if ($assessmentCourse->due_at && Carbon::parse($assessmentCourse->due_at)->isPast()) {
                continue;
            }

            $this->regradeAssessmentCourse($assessmentCourse);
        }
    }

    public function regradeAssessmentCourse(AssessmentCourse $assessmentCourse): void
    {
        $is_specification = Settings::sole()->specification_grading;
        $threshold = Settings::sole()->specification_grading_threshold;

        $assessment = $assessmentCourse->assessment;
        $course = $assessmentCourse->course;
        $assessment_canvas_id = $assessmentCourse->assessment_canvas_id;

        if ($is_specification) {
            $pointsPossible = 1;
        } else {
            $pointsPossible = $assessment->questionCount();
        }
        CanvasService::editAssignment($course->id, $assessment_canvas_id,
            ['points_possible' => $pointsPossible]
        );

        $users = $course->users;
        foreach ($users as $user) {
            $grade = $assessmentCourse->gradeForUser($user);

            if ($is_specification) {
                $grade = $grade >= $threshold ? 1 : 0;
            }

            if ($grade == 0) {
                continue;
            }

            CanvasService::gradeAssignment($course->id, $assessment_canvas_id, $user->canvas->canvas_id, $grade);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.specification-setting');
    }
}
