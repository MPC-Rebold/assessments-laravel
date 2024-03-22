<?php

use Livewire\Volt\Component;
use App\Models\Master;
use Illuminate\Support\Collection;

new class extends Component {
    public Master $master;
    public Collection $assessments;

    public function mount(Master $master): void
    {
        $this->master = $master;
        $this->assessments = $master->assessments;
    }
}; ?>

<div class="bg-slate-100 shadow sm:rounded-lg">
    <div class="flex items-center bg-white p-4 shadow sm:rounded-lg sm:px-6 sm:py-4">
        <div class="text-lg font-bold">
            Assessments
        </div>
    </div>
    <div>
        @if ($assessments->isEmpty())
            <div class="text-center p-4 sm:px-6">
                <p class="text-lg font-bold text-gray-400">
                    No Assessments
                </p>
            </div>
        @else
            @foreach ($assessments as $assessment)
                <a href="{{route('assessment.edit', [$master->id, $assessment->id])}}" wire:navigate>
                    <div
                        class="flex flex-wrap items-center justify-between gap-4 rounded-lg px-4 py-3 hover:bg-gray-200 sm:px-6 transition-all hover:shadow group">
                        <div class="flex items-center space-x-4">
                            <div class="group-hover:underline">
                                {{ $assessment->title }}
                            </div>
                            <div class="hidden sm:flex">
                                <span class="text-gray-500">
                                    Questions: {{ $assessment->questions->count() }}
                                </span>
                            </div>
                        </div>
                        <x-button secondary icon="pencil" class="group-hover:scale-[1.05]">
                            Edit
                        </x-button>
                    </div>
                </a>
                @if (!$loop->last)
                    <hr class="mx-4 sm:mx-6">
                @endif
            @endforeach
    </div>
    @endif
</div>
