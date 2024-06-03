<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\AssessmentCourse;
use App\Services\CanvasService;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public User $user;
    public AssessmentCourse $assessmentCourse;

    public function mount(User $user, AssessmentCourse $assessmentCourse): void
    {
        $this->user = $user;
        $this->assessmentCourse = $assessmentCourse;
    }

    public function submitToCanvas(): void
    {
        try {
            $gradeResponse = CanvasService::gradeAssessmentForUser($this->assessmentCourse, auth()->user());

            if ($gradeResponse->status() !== 200) {
                throw new Exception('Status: ' . $gradeResponse->status() . '. Ensure the user is enrolled in the associated course.');
            }
        } catch (Exception $e) {
            $this->notification()->error('Failed to submit to Canvas', $e->getMessage());
            return;
        }
        $this->notification()->success('Submitted to Canvas', 'Grade: ' . $gradeResponse->json('grade'));
    }
}; ?>

<div class="group flex w-full items-center justify-between bg-white p-4 shadow sm:rounded-lg sm:px-6 sm:py-4">
    <div>
        <b>
            Grade: {{ $assessmentCourse->pointsForUser($user) }}
            / {{ $assessmentCourse->assessment->questionCount() }}
        </b>
    </div>
    <x-button positive spinner class="min-w-40" wire:click="submitToCanvas">
        <p class="text-sm">
            Sync to Canvas
        </p>
    </x-button>
</div>
