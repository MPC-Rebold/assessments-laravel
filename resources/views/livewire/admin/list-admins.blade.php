<?php

use Livewire\Volt\Component;
use App\Services\SeedService;

new class extends Component {
    public array $admins;

    public function mount(): void
    {
        $this->admins = SeedService::getAdmins();
    }
}; ?>

<div class="bg-white p-4 shadow sm:rounded-lg sm:px-6 sm:py-2">
    <div class="flex min-h-10 items-center space-x-4">
        <div class="text-lg font-bold">
            Admins:
        </div>
        <div class="text-gray-500">
            {{ implode(', ', $admins) }}
        </div>
    </div>
</div>
