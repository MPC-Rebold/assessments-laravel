<?php

use App\Models\Settings;
use App\Services\CanvasService;
use Livewire\Volt\Component;
use Carbon\Carbon;
use Livewire\Attributes\On;

new class extends Component {
    public string $lastSyncedAt;
    public bool $apiKeyValid;
    public string $apiKeyName;
    public array $activeCourses;
    public bool $activeCoursesModalOpen;

    public function mount(): void
    {
        $this->lastSyncedAt = Settings::first()->last_synced_at;
        $canvasSelf = CanvasService::getSelf();
        $this->apiKeyValid = $canvasSelf->status() !== 401;
        $this->apiKeyName = $canvasSelf->json()['name'];
        $this->activeCourses = CanvasService::getCourses();
        // add random entries to $this->activeCourses
        for ($i = 0; $i < 50; $i++) {
            $this->activeCourses[] = [
                'id' => $i,
                'name' => 'Course ' . $i,
            ];
        }

        $this->activeCoursesModalOpen = false;
    }

    #[On('updateLastSyncedAt')]
    public function updateLastSyncedAt(): void
    {
        $this->lastSyncedAt = Settings::first()->last_synced_at;
    }

    public function openActiveCoursesModal(): void
    {
        $this->activeCoursesModalOpen = true;
    }

    public function closeActiveCoursesModal(): void
    {
        $this->activeCoursesModalOpen = false;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="p-4 sm:px-6 space-y-2">
            <div class="flex ic justify-between">
                <div>
                    Last Synced
                </div>
                <x-spinner />
            </div>
            <hr>
            <div class="flex items-center justify-between">
                <div>
                    Api Key Status
                </div>
                <x-spinner />
            </div>
            <hr>
            <div class="flex items-center justify-between">
                <div>
                    Active Canvas Courses
                </div>
                <x-spinner />
            </div>
        </div>
        HTML;
    }
}; ?>

<div class="space-y-2 p-4 sm:px-6">
    <div class="flex items-center justify-between">
        <div>
            Last Synced
        </div>
        <div>
            {{ $lastSyncedAt ? Carbon::parse($lastSyncedAt)->tz('PST')->format('Y-m-d H:i:s T') : 'Never' }}
        </div>
    </div>
    <hr>
    <div class="flex items-center justify-between">
        <div>
            Api Key Status
        </div>
        <div class="flex items-center">
            @if ($apiKeyValid)
                <div class="text-positive-500">Valid ({{ $apiKeyName }})</div>
                <x-icon name="check" class="h-6 w-6 text-positive-500" />
            @else
                <div class="text-negative-500">Invalid</div>
                <x-icon name="x" class="h-6 w-6 text-negative-500" />
            @endif
        </div>
    </div>
    <hr>
    <a class="flex cursor-pointer items-center justify-between hover:text-secondary-500 hover:underline"
        wire:click="openActiveCoursesModal">
        <div>
            Active Canvas Courses
        </div>
        <div class="flex items-center space-x-1">
            <div>
                {{ count($activeCourses) }}
            </div>
            <x-icon name="information-circle" class="h-6 w-6 text-secondary-500" />
        </div>
    </a>
    <x-modal wire:model.defer="activeCoursesModalOpen">
        <x-card title="View Active Canvas Courses">
            <div class='rounded-lg border border-secondary-600 bg-secondary-50 p-4'>
                <div class="flex items-center border-b-2 border-secondary-200 pb-3">
                    <x-icon name="information-circle" class="h-6 w-6 text-secondary-700" />
                    <div class="ml-1 text-lg text-secondary-700">
                        Active Canvas Courses ({{ count($activeCourses) }})
                    </div>
                </div>
                <div class="ml-5 mt-2 flex items-center justify-between pl-1">
                    <ul class="max-h-[40vh] w-full list-inside list-disc space-y-1 overflow-auto text-secondary-700">
                        @foreach ($activeCourses as $course)
                            <li>
                                <a class="hover:underline"
                                    href="{{ config('canvas.host') . '/courses/' . $course['id'] }}">
                                    {{ $course['name'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <x-slot name="footer">
                <div class="w-full">
                    <x-button secondary label="Close" class="w-full" wire:click="closeActiveCoursesModal" />
                </div>
            </x-slot>
        </x-card>
    </x-modal>
</div>
