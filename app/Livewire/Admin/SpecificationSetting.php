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
        $assessmentCourses = AssessmentCourse::all();

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

    public function setMaxPoints(AssessmentCourse $assessmentCourse): void
    {
        if ($assessmentCourse->due_at && Carbon::parse($assessmentCourse->due_at)->isPast()) {
            return;
        }

        $is_specification = $assessmentCourse->course->specification_grading;

        $assessment = $assessmentCourse->assessment;
        $course = $assessmentCourse->course;
        $assessment_canvas_id = $assessmentCourse->assessment_canvas_id;

        CanvasService::editAssignment($course->id, $assessment_canvas_id,
            [
                'points_possible' => $is_specification ? 0 : $assessment->questionCount(),
                'grading_type' => $is_specification ? 'pass_fail' : 'points',
            ]
        );
    }

    public function regradeAssessmentCourse(AssessmentCourse $assessmentCourse, bool $regradePastDue = true): void
    {
        if ($assessmentCourse->due_at && Carbon::parse($assessmentCourse->due_at)->isPast() && ! $regradePastDue) {
            return;
        }

        $is_specification = $this->course->specification_grading;
        $threshold = $this->course->specification_grading_threshold;

        $course = $assessmentCourse->course;
        $assessment_canvas_id = $assessmentCourse->assessment_canvas_id;

        $this->setMaxPoints($assessmentCourse);

        //        $users = $course->users;
        //        foreach ($users as $user) {
        //            $grade = $assessmentCourse->gradeForUser($user);
        //
        //            if ($is_specification) {
        //                $grade = $grade >= $threshold ? 1 : 0;
        //            }
        //
        //            CanvasService::gradeAssignment($course->id, $assessment_canvas_id, $user->canvas->canvas_id, $grade);
        //        }

        CanvasService::gradeAssignment($course->id, $assessment_canvas_id);
    }

    public function render(): View
    {
        return view('livewire.admin.specification-setting');
    }
}
