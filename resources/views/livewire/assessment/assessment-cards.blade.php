<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use App\Models\Course;

new class extends Component {
    public array $assessments;
}; ?>

<div class="space-y-4">
    @foreach($assessments as $assessment)
        <div class="px-6 py-4 text-gray-900 bg-white overflow-hidden shadow-sm sm:rounded-lg hover:scale-[1.007] transition-all">
                <div class="flex justify-between items-center">
                    <a class="hover:underline" href="{{ route('assessment', [$assessment["course_id"], $assessment["id"]]) }}" wire:navigate>
                        <div>
                            <div class="text-lg font-semibold">
                                {{ $assessment["title"] }}
                            </div>
                            <div class="text-gray-500 text-sm">
                                {{ Course::find($assessment["course_id"])->title }}
                            </div>
                        </div>
                    </a>

                    <div class="flex space-x-4">
                        <x-canvas-button class="w-10 h-10" :href="'/courses/' . $assessment['course_id'] . '/assignments/' . $assessment['id']"/>
                        <x-button secondary :href="route('assessment', [$assessment['course_id'],  $assessment['id']])" wire:navigate class="relative">
                            <span class="transition-transform duration-300">Go</span>
                            <x-icon class="w-5 h-5 transition-transform transform translate-x-0 group-hover:translate-x-1" name="arrow-right" />
                        </x-button>

                    </div>
                </div>
        </div>
    @endforeach
</div>
