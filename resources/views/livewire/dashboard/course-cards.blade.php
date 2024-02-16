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

<div>
    @foreach($courses as $course)
        <a href="{{ $course['href'] }}" wire:navigate class="hover:text-indigo-700">
            {{ $course['title'] }}
        </a>
    @endforeach
</div>
