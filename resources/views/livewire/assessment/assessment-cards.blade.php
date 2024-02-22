<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Master;

new class extends Component {
    public Collection $assessments;
}; ?>

<div class="space-y-4">
    @if ($assessments->isNotEmpty())
        @foreach ($assessments as $assessment)
            <div
                class="overflow-hidden bg-white px-6 py-4 text-gray-900 shadow-sm transition-all hover:scale-[1.007] sm:rounded-lg">
                <div class="flex items-center justify-between">
                    <a class="hover:underline"
                        href="{{ route('assessment.show', [$assessment->pivot->course_id, $assessment->pivot->assessment_canvas_id]) }}"
                        wire:navigate>
                        <div>
                            <div class="text-lg font-semibold">
                                {{ $assessment->title }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ Master::find($assessment->master_id)->title }}
                            </div>
                        </div>
                    </a>

                    <div class="flex space-x-4">
                        <x-canvas-button class="h-10 w-10" :href="'/courses/' .
                            $assessment->pivot->course_id .
                            '/assignments/' .
                            $assessment->pivot->assessment_canvas_id" />
                        <x-button secondary :href="route('assessment.show', [
                            $assessment->pivot->course_id,
                            $assessment->pivot->assessment_canvas_id,
                        ])" wire:navigate class="relative">
                            <span class="transition-transform duration-300">Go</span>
                            <x-icon
                                class="h-5 w-5 translate-x-0 transform transition-transform group-hover:translate-x-1"
                                name="arrow-right" />
                        </x-button>

                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="flex max-h-20 w-full items-center justify-between px-6 py-4 text-gray-900">
                No assessments found
            </div>
        </div>
    @endif
</div>
