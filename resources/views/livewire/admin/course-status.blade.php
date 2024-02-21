<?php

use Livewire\Volt\Component;
use App\Models\Master;
use App\Models\Course;

new class extends Component {
    public Master $masterCourse;
    public array $connectedCourses;
    public array $allAvailableCourses;


    public function mount(): void
    {
        $this->allAvailableCourses = Course::all()->whereNull('master_id')->pluck('title')->toArray();
        $this->connectedCourses = $this->masterCourse->courses->all();
    }
}; ?>

<div>
    {{ $masterCourse->title }}
</div>
