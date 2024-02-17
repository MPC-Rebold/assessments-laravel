<?php

use Livewire\Volt\Component;
use App\Models\Course;

new class extends Component {
    public function with(): array
    {
        return [
            'courses' => auth()->user()->courses->map(fn($course) => [
                'title' => $course->title,
                'id' => $course->id,
                'href' => route('course', $course['id']),
            ]),
        ];
    }
}; ?>

<div>
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
                        <x-button secondary :href="route('course', $course['id'])">
                            Go
                            <x-icon class="w-5 h-5" name="arrow-right"/>
                        </x-button>
                    </div>
                </div>
            </div>
        </div>

    @endforeach
</div>
