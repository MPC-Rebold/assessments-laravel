<?php

use Livewire\Volt\Component;
use App\Models\Course;
use Illuminate\Support\Collection;

new class extends Component {
    public Collection $courses;

    public function mount(): void
    {
        $this->courses = auth()->user()->courses;
    }
}; ?>

<div>
    @if (count($courses) > 0)
        <div class="space-y-4">
            @foreach ($courses as $course)
                <div class="transition-all hover:scale-[1.007]">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="flex max-h-20 w-full items-center justify-between p-6 text-gray-900">
                            <a href="{{ route('course.show', $course->id) }}" wire:navigate>
                                <div class="group me-2 flex flex-wrap items-baseline gap-x-2 py-2">
                                    <h2 class="text-xl font-semibold group-hover:underline">
                                        {{ $course->master->title }}
                                    </h2>
                                    <h2 class="text-gray-500">
                                        ({{ $course->title }})
                                    </h2>
                                </div>
                            </a>

                            <div class="flex space-x-4">
                                <x-canvas-button class="h-10 w-10" :href="'/courses/' . $course->id" />
                                <x-button positive :href="route('course.show', $course->id)" wire:navigate class="relative">
                                    <span>Go</span>
                                    <x-icon
                                        class="h-5 w-5 translate-x-0 transform transition-transform group-hover:translate-x-1"
                                        name="arrow-right" />
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="flex max-h-20 w-full items-center justify-between px-6 py-4 text-gray-900">
                No courses found
            </div>
        </div>
    @endif
</div>
