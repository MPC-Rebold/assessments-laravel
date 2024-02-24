<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Master;

new class extends Component {
    public Collection $assessments;
}; ?>

<div class="space-y-4">
    <x-progress-circle class="h-16 w-16" :percentge="25" />

    @if ($assessments->isNotEmpty())
        @foreach ($assessments as $assessment)
            <div
                class="overflow-hidden bg-white px-6 py-4 text-gray-900 shadow-sm transition-all hover:scale-[1.007] sm:rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a class="hover:underline"
                            href="{{ route('assessment.show', [$assessment->pivot->course_id, $assessment->pivot->assessment_canvas_id]) }}"
                            wire:navigate>
                            <div>
                                <div class="text-lg font-semibold">
                                    {{ $assessment->title }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ Course::find($assessment->pivot->course_id)->title }}
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="text-slate-500" wire:poll.keep-alive wire:poll.15s>
                            @if ($assessment->pivot->due_at)
                                Due in:
                                @php($diff = Carbon::now()->diff(Carbon::parse($assessment->pivot->due_at)))
                                @if ($diff->d === 1)
                                    {{ $diff->d }} day
                                @elseif($diff->d > 1)
                                    {{ $diff->d }} days
                                @endif
                                @if ($diff->h)
                                    @if ($diff->h === 1)
                                        {{ $diff->h }} hour
                                    @else
                                        {{ $diff->h }} hours
                                    @endif
                                @else
                                    @if ($diff->i === 1)
                                        {{ $diff->i }} minute
                                    @else
                                        {{ $diff->i }} minutes
                                    @endif
                                @endif
                            @else
                                No due date
                            @endif
                        </div>
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
