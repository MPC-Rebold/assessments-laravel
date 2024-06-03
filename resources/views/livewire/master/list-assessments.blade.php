<?php

use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use App\Models\Master;
use Illuminate\Support\Collection;
use App\Livewire\Admin\Sync;
use App\Models\Assessment;

new class extends Component {
    public Master $master;
    public Collection $assessments;

    public function mount(Master $master): void
    {
        $this->master = $master;
        $this->assessments = $master->assessments->sort(function ($a, $b) {
            preg_match('/(\d+)$/', $a->title, $matchesA);
            preg_match('/(\d+)$/', $b->title, $matchesB);

            $numA = isset($matchesA[1]) ? (int) $matchesA[1] : 0;
            $numB = isset($matchesB[1]) ? (int) $matchesB[1] : 0;

            $titleA = preg_replace('/\d+$/', '', $a->title);
            $titleB = preg_replace('/\d+$/', '', $b->title);

            if ($titleA === $titleB) {
                return $numA <=> $numB;
            }

            return strcmp($titleA, $titleB);
        });
        $this->uploadedAssessments = [];
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
            <div class="p-4 text-center sm:px-6">
                <p class="text-lg font-bold text-gray-400">
                    No Assessments
                </p>
            </div>
        @else
            @foreach ($assessments as $assessment)
                <a href="{{ route('assessment.edit', [$master->id, $assessment->id]) }}" wire:navigate>
                    <div
                        class="group flex flex-wrap items-center justify-between gap-4 rounded-lg px-4 py-3 transition-all hover:bg-gray-200 hover:shadow sm:px-6">
                        <div class="flex items-center space-x-4">
                            <div class="group-hover:underline">
                                {{ $assessment->title }}
                            </div>
                            <div class="hidden sm:flex">
                                <span class="text-gray-500">
                                    Questions:
                                    {{ $assessment->questions->count() }}
                                </span>
                            </div>
                        </div>
                        <x-button secondary icon="pencil" class="group-hover:scale-[1.05]">
                            Edit
                        </x-button>
                    </div>
                </a>
                <hr class="mx-4 sm:mx-6">
            @endforeach
        @endif
        <livewire:master.upload-assessments :master="$master" />
    </div>
</div>
