<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Collection;

new class extends Component {
    public Course|null $course;

    public Collection $students;
    public Collection $courses;
    public array $notEnrolledStudents;
    public array $validStudents;

    public function mount(): void
    {
        if (!isset($this->course)) {
            $this->courses = Course::all();
        } else {
            $this->courses = collect([$this->course]);
        }

        $this->students = $this->courses->flatMap->users->sortBy('name')->unique('email');
        $this->validStudents = array_unique($this->courses->flatMap->valid_students->toArray());
        sort($this->validStudents);

        $this->notEnrolledStudents = array_diff($this->validStudents, $this->students->pluck('email')->toArray());
    }
}; ?>

<div class="bg-slate-100 shadow sm:rounded-lg">
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-4 shadow sm:rounded-lg sm:px-6 sm:py-4">
        <div class="text-lg font-bold">
            Students
        </div>
    </div>

    <div>
        @if ($students->isEmpty() && empty($notEnrolledStudents))
            <div class="text-center">
                <p class="text-lg font-bold text-gray-400">
                    No Students
                </p>
            </div>
        @else
            @foreach ($students as $enrolledStudent)
                <a href="{{ route('user.show', $enrolledStudent->id) }}" wire:navigate>
                    <div
                        class="group flex flex-wrap items-center justify-between gap-4 rounded-lg px-4 py-3 transition-all hover:bg-gray-200 hover:shadow sm:px-6">
                        <div class="flex items-center space-x-2">
                            <x-avatar xs :src="$enrolledStudent->avatar" class="shrink-0" />
                            <div class="group-hover:underline">
                                {{ $enrolledStudent->email }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="hidden text-gray-500 md:flex">
                                <p>{{ $enrolledStudent->name }}
                                    @if ($enrolledStudent->is_admin)
                                        <span class="text-sm text-negative-600">(ADMIN)</span>
                                    @endif
                                </p>
                            </div>
                            <x-icon name="chevron-right"
                                    class="h-5 text-gray-500 transition-all group-hover:translate-x-1 group-hover:scale-110" />
                        </div>
                    </div>
                </a>
                @if (!empty($notEnrolledStudents))
                    <hr class="mx-4 sm:mx-6" />
                @endif
            @endforeach
            @foreach ($notEnrolledStudents as $notEnrolledStudent)
                <div class="flex items-center justify-between px-4 py-3 sm:px-6">
                    <div class="flex items-center space-x-2">
                        <x-badge.circle secondary icon="ban" />
                        <div>
                            {{ $notEnrolledStudent }}
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="hidden text-gray-500 md:flex">
                            - Not Connected -
                        </div>
                        <x-icon name="chevron-right" class="invisible h-5" />
                    </div>
                </div>
                @if (!$loop->last)
                    <hr class="mx-4 sm:mx-6" />
                @endif
            @endforeach
    </div>
    @endif
</div>
