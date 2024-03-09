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

    <div class="p-4 sm:px-6 sm:py-4">
        @if ($assessments->isEmpty())
            <div class="text-center">
                <p class="text-lg font-bold text-gray-400">
                    No Assessments
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($assessments as $assessment)
                    <div class="flex items-center justify-between">
                        <div>
                            {{ $assessment->title }}
                        </div>
                        <x-button secondary icon="pencil" :href="route('assessment.edit', [$master->id, $assessment->id])">
                            Edit
                        </x-button>
                    </div>
                    @if (!$loop->last)
                        <hr>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
