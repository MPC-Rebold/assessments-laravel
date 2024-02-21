<?php

use Livewire\Volt\Component;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    public Collection $courses;

    public function mount(): void
    {
        $this->courses = auth()->user()->courses->map(
            fn($course) => [
                'title' => $course->title,
                'id' => $course->id,
                'href' => route('course', $course['id']),
            ],
        );
    }
}; ?>

<div>
    @if (count($courses) > 0)
        @foreach ($courses as $course)
            <div class="transition-all hover:scale-[1.007]">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="flex max-h-20 w-full items-center justify-between p-6 text-gray-900">
                        <a href="{{ $course['href'] }}" wire:navigate>
                            <h2 class="py-2 text-xl font-semibold hover:underline">
                                {{ $course['title'] }}
                            </h2>
                        </a>

                        <div class="flex space-x-4">
                            <x-canvas-button class="h-10 w-10" :href="'/courses/' . $course['id']" />
                            <x-button secondary :href="route('course', $course['id'])" wire:navigate class="relative">
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
    @else
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="flex max-h-20 w-full items-center justify-between px-6 py-4 text-gray-900">
                No courses found
            </div>
        </div>
    @endif
</div>
