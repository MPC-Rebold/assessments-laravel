<?php

use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'assessments' => array_slice(auth()->user()->assessments(), 0, 4),
        ];
    }
}; ?>

<div>
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
