<?php

use Livewire\Volt\Component;
use App\Models\Course;

new class extends Component {
    public function with(): array
    {
        return [
            'userCourses' => auth()->user()->courses->toArray(),
        ];
    }
}; ?>

<div>
    @foreach($userCourses as $course)
        {{ $course["title"] }}
    @endforeach
</div>
