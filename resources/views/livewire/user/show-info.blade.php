<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Course;
use App\Models\AssessmentCourse;
use Illuminate\Support\Collection;

new class extends Component {
    public User $user;

    public string $assessmentSelect;
    public string $courseSelect;
    public Collection $assessmentOptions;
    public Collection $courseOptions;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->courseOptions = $user->courses;
        //        $this->assessmentOptions = $user->courses->flatMap->assessments;
    }

    public function populateAssessmentOptions(): void
    {
        if (isset($this->courseSelect)) {
            $this->assessmentOptions = Course::find($this->courseSelect)->assessments;
        } else {
            $this->assessmentOptions = collect();
        }
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-4 bg-white p-4 shadow sm:rounded-lg sm:p-6">
        <div class="flex justify-between align-middle">
            <div class="flex items-center justify-between">
                <x-avatar xl :src="$user->avatar" class="mx-auto h-fit" />
                <div class="ms-4">
                    <h1 class="text-xl font-bold text-gray-800">{{ $user->name }}</h1>
                    <p class="text-gray-600">{{ $user->email }}</p>
                </div>
            </div>
            @if ($user->is_admin)
                <div class="text-red-500">
                    ADMIN
                </div>
            @else
                <div class="text-gray-800">
                    Student
                </div>
            @endif
        </div>
        <div>
            <p>
                <b>Canvas ID:</b>
                {{ $user->canvas ? $user->canvas->canvas_id : 'N/A' }}
            </p>
            <p>
                <b>Enrolled Courses:</b>
                @if ($user->courses->count() > 0)
                    {{ implode(', ', $user->courses->pluck('title')->toArray()) }}
                @else
                    No enrolled courses
                @endif
            </p>
        </div>
    </div>
    <div class="bg-slate-100 shadow sm:rounded-lg">

        <div class="flex flex-wrap items-center justify-between space-y-2 bg-white p-4 sm:rounded-lg sm:p-6">
            <p class="text-lg font-bold">
                View Grade For:
            </p>
            <div wire:click="populateAssessmentOptions" class="block">
                <x-select placeholder="Course" :options="$courseOptions" class="w-96" wire:model.defer="courseSelect"
                    :option-value="'id'" :option-label="'title'" />
            </div>
            <x-select placeholder="Assessment" :options="$assessmentOptions" class="w-96" wire:model.defer="assessmentSelect"
                :option-value="'id'" :option-label="'title'" />
        </div>
        <div
            class="{{ isset($assessmentSelect) ? 'max-h-96' : 'invisible max-h-0' }} overflow-hidden transition-all ease-in-out">
        </div>
    </div>
</div>
