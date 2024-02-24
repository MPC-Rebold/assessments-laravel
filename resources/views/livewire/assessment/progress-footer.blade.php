<?php

use Livewire\Volt\Component;
use App\Models\AssessmentCourse;

new class extends Component {
    public AssessmentCourse $assessmentCourse;

    public int $percentage;
    public int $points;

    public function mount(): void
    {
        $this->points = $this->assessmentCourse->pointsForUser(auth()->user());
        $this->percentage = ($this->points / $this->assessmentCourse->assessment->questionCount()) * 100;
    }
}; ?>

<footer class="fixed bottom-0 mx-auto w-full bg-slate-200 px-4 py-2 shadow-inner sm:px-6 lg:px-8">
    <div class="flex items-center justify-between pl-2">
        <div class="h-2.5 w-full rounded-full bg-white dark:bg-gray-700">
            <div class="h-2.5 rounded-full bg-positive-500 transition-all ease-out" style="width: {{ $percentage }}%">
            </div>
        </div>
        <button class="ml-4 min-w-20 text-xl font-extrabold transition-all ease-in-out hover:scale-125"
            x-data="{ percentage: false }" @click="percentage = ! percentage">
            <div :class="{ 'hidden': !percentage }">
                {{ $percentage }}%
            </div>
            <div :class="{ 'hidden': percentage }">
                {{ $points }} / {{ $assessmentCourse->assessment->questionCount() }}
            </div>
        </button>
    </div>
</footer>
