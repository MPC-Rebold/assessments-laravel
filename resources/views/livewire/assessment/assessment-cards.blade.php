<?php

use Livewire\Volt\Component;
use App\Models\Assessment;

new class extends Component {
    public array $assessments;
}; ?>

<div class="space-y-4">
    @foreach($assessments as $assessment)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <a href="{{ route('assessment', [$assessment["course_id"], $assessment["id"]]) }}" wire:navigate class="hover:text-indigo-700">
                    {{ $assessment['title'] }}
                </a>
            </div>
        </div>
    @endforeach
</div>
