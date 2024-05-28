<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Course;
use App\Models\AssessmentCourse;
use Illuminate\Support\Collection;

new class extends Component {
    public User $user;

    public string|null $courseSelect;
    public Course $courseShow;
    public Collection $courseOptions;

    public Collection $assessments;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->courseOptions = $user->courses;
    }

    public function fetchGrades(): void
    {
        $this->assessments = AssessmentCourse::where('course_id', $this->courseSelect)->get();
        $this->courseShow = Course::find($this->courseSelect);
        $this->courseSelect = null;
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-4 bg-white p-4 shadow sm:rounded-lg sm:p-6">
        <div class="flex justify-between align-middle">
            <div class="flex items-center justify-between">
                <x-avatar xl :src="$user->avatar" class="mx-auto h-fit" />
                <div class="ms-4">
                    <h1 class="text-xl font-bold text-gray-800">
                        {{ $user->name }}</h1>
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
        <div
            class="{{ isset($assessments) ? 'shadow' : '' }} flex flex-wrap items-center justify-between gap-2 bg-white p-4 sm:rounded-lg sm:p-6">
            <p class="text-lg font-bold">
                View Grades For: @if (isset($assessments))
                    <span class="text-gray-500">{{ $courseShow->title }}</span>
                @endif
            </p>
            <form class="flex flex-wrap items-center gap-4">
                <div class="w-60 sm:w-80">
                    <x-select placeholder="Course" :options="$courseOptions" wire:model.defer="courseSelect" :option-value="'id'"
                        :option-label="'title'" />
                </div>
                <x-button secondary spinner class="min-w-24" wire:click="fetchGrades" disabled
                    wire:dirty.attr.remove="disabled">
                    View
                </x-button>
            </form>
        </div>
        <div
            class="{{ isset($assessments) ? 'max-h-[999vh]' : 'invisible max-h-0' }} overflow-hidden transition-all duration-500 ease-in-out">
            @if (isset($assessments))
                @if ($assessments->count() === 0)
                    <div class="p-4 text-center">
                        <p class="text-lg font-bold text-gray-400">
                            No assessments found
                        </p>
                    </div>
                @else
                    <div>
                        @foreach ($assessments as $assessment)
                            <a href="{{ route('user.grade.show', [$user->id, $assessment->id]) }}" wire:navigate>
                                <div
                                    class="group flex items-center justify-between rounded-lg px-4 py-3 transition-all hover:bg-gray-200 hover:shadow sm:px-6">
                                    <div class="group-hover:underline">
                                        {{ $assessment->assessment->title }}
                                    </div>
                                    <div class="flex items-center gap-6">
                                        <div>
                                            {{ $assessment->pointsForUser($user) }}
                                            /
                                            {{ $assessment->assessment->questionCount() }}
                                        </div>
                                        <x-icon name="chevron-right"
                                            class="h-5 text-gray-500 transition-all group-hover:translate-x-1 group-hover:scale-110" />
                                    </div>
                                </div>
                            </a>
                            @if (!$loop->last)
                                <hr class="mx-4 sm:mx-6" />
                            @endif
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
