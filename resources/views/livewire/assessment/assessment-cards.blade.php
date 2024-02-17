<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use App\Models\Course;

new class extends Component {
    public array $assessments;
}; ?>

<div class="space-y-4">
    @foreach($assessments as $assessment)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 text-gray-900">
                <a href="{{ route('assessment', [$assessment["course_id"], $assessment["id"]]) }}" wire:navigate>
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-lg font-semibold">
                                {{ $assessment["title"] }}
                            </div>
                            <div class="text-gray-500 text-sm">
                                {{ Course::find($assessment["course_id"])->title }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    @endforeach
</div>
