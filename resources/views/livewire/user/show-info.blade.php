<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }
}; ?>

<div>
    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
        <div class="flex justify-between align-middle">
            <div class="flex items-center justify-between">
                <x-avatar xl :src="$user->avatar" class="mx-auto h-fit" />
                <div class="ms-4">
                    <h1 class="text-xl font-bold text-gray-800">{{ $user->name }}</h1>
                    <p class="text-gray-600">{{ $user->email }}</p>
                </div>
            </div>
            @if ($user->is_admin)
                <div class="text-red-500">
                    ADMIN
                </div>
            @else
                <div class="text-gray-800">
                    Student
                </div>
            @endif
        </div>
    </div>
</div>
