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
    public string $search = '';

    public function mount(): void
    {
        if (!isset($this->course)) {
            $this->courses = Course::all();
        } else {
            $this->courses = collect([$this->course]);
        }

        $this->students = $this->courses->flatMap->users
            ->filter(function ($student) {
                return str_contains(strtolower($student->email), strtolower($this->search)) || str_contains(strtolower($student->name), strtolower($this->search));
            })
            ->sortBy('name');

        $this->validStudents = array_unique(
            array_filter($this->courses->flatMap->valid_students->toArray(), function ($student) {
                return str_contains(strtolower($student), strtolower($this->search));
            }),
        );

        $this->notEnrolledStudents = array_diff($this->validStudents, $this->students->pluck('email')->toArray());
    }

    public function updateSearch(): void
    {
        $this->mount();
    }
}; ?>

<div class="bg-slate-100 shadow sm:rounded-lg">
    <div class="flex items-center justify-between bg-white p-4 shadow sm:rounded-lg sm:px-6 sm:py-4">
        <div class="text-lg font-bold">
            Students
        </div>
        <div class="flex items-center justify-between space-x-4">
            <div class="w-64">
                <x-input right-icon="search" placeholder="Search" wire:model.defer="search" />
            </div>
            <x-button secondary wire:click="updateSearch">
                Search
            </x-button>
        </div>
    </div>

    <div class="p-4 sm:px-6 sm:py-4">
        @if ($students->isEmpty() && empty($notEnrolledStudents))
            <div class="text-center">
                <p class="text-lg font-bold text-gray-400">
                    No Students
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($students as $enrolledStudent)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <x-avatar xs :src="$enrolledStudent->avatar" />
                            <div class="min-w-80">
                                <p class="overflow-hidden text-ellipsis">
                                    {{ $enrolledStudent->email }}
                                </p>
                            </div>
                            <div class="hidden text-gray-500 sm:flex">
                                <p>{{ $enrolledStudent->name }}
                                    @if ($enrolledStudent->is_admin)
                                        <span class="text-sm text-negative-600">(ADMIN)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <x-button secondary class="min-w-24 !p-[3px]" :href="route('user.show', auth()->user()->id)" wire:navigate>
                            <div class="group flex items-center space-x-2">
                                <div>Manage</div>
                                <div>
                                    <x-icon name="chevron-right"
                                        class="h-4 w-4 transition-all ease-in-out group-hover:translate-x-1" />
                                </div>
                            </div>
                        </x-button>
                    </div>
                    @if (!empty($notEnrolledStudents))
                        <hr />
                    @endif
                @endforeach
                @foreach ($notEnrolledStudents as $notEnrolledStudent)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">

                            <x-badge.circle warning icon="exclamation" class="animate-pulse" />
                            <div class="min-w-80">
                                <p class="overflow-hidden text-ellipsis">
                                    {{ $notEnrolledStudent }}
                                </p>
                            </div>
                            <div class="hidden text-warning-500 sm:flex">
                                Not Connected
                            </div>
                        </div>
                    </div>
                    @if (!$loop->last)
                        <hr />
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
