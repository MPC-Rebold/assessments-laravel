<?php

use Livewire\Volt\Component;
use App\Models\Course;

new class extends Component {
    public function with(): array
    {
        return [
            'courses' => auth()->user()->courses->map(fn($course) => [
                'title' => $course->title,
                'href' => route('courses', $course['id']),
            ]),
        ];
    }
}; ?>

<div class="space-y-2">
    @foreach($courses as $course)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <a href="{{ $course['href'] }}" wire:navigate class="hover:text-indigo-700">
                    {{ $course['title'] }}
                </a>
            </div>
        </div>
    @endforeach
</div>
