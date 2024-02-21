<?php

use Livewire\Volt\Component;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    public Collection $courses;

    public function mount(): void
    {
        $this->courses = auth()->user()->courses->map(fn($course) => [
            'title' => $course->title,
            'id' => $course->id,
            'href' => route('course', $course['id'])
        ]);
    }
}; ?>

<div>
    @if (count($courses) > 0)
        @foreach($courses as $course)
            <div class="hover:scale-[1.007] transition-all">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 w-full max-h-20 flex justify-between items-center">
                        <a href="{{$course['href']}}" wire:navigate>
                            <h2 class="py-2 text-xl font-semibold hover:underline">
                                {{ $course['title'] }}
                            </h2>
                        </a>

                        <div class="flex space-x-4">
                            <x-canvas-button class="w-10 h-10" :href="'/courses/'.$course['id']"/>
                            <x-button secondary :href="route('course', $course['id'])" wire:navigate class="relative">
                                <span>Go</span>
                                <x-icon class="w-5 h-5 transition-transform transform translate-x-0 group-hover:translate-x-1" name="arrow-right" />
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 text-gray-900 w-full max-h-20 flex justify-between items-center">
                    No courses found
            </div>
        </div>
    @endif
</div>
