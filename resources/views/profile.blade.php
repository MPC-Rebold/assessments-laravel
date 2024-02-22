<?php

use App\Models\Master;
use App\Models\Course;

?>

@section('title', 'Profile')

<x-app-layout>
    <livewire:layout.header :routes="[['title' => 'Profile', 'href' => route('profile')]]" />

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
                <div class="flex justify-between align-middle">
                    <div class="flex justify-between">
                        <x-avatar xl :src="auth()->user()->avatar" class="mx-auto" />
                        <div class="ms-4">
                            <h1 class="text-xl font-bold text-gray-800">{{ auth()->user()->name }}</h1>
                            <p class="text-gray-600">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    @if (auth()->user()->is_admin)
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
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                @if (auth()->user()->is_admin)
                    <div class="space-y-2">
                        <div class="flex">
                            <h2 class="min-w-fit font-bold text-gray-800">Master Courses:</h2>
                            <div class="ms-1 text-wrap">
                                @if (Master::count() > 0)
                                    {{ implode(', ', Master::pluck('title')->toArray()) }}
                                @else
                                    There are no courses. Try syncing with Canvas.
                                @endif
                            </div>
                        </div>
                        <div class="flex">
                            <h2 class="min-w-fit font-bold text-gray-800">Canvas Courses:</h2>
                            <div class="ms-1 text-wrap">
                                @if (Course::count() > 0)
                                    {{ implode(', ', Course::pluck('title')->toArray()) }}
                                @else
                                    There are no courses. Try syncing with Canvas.
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex">

                        <h2 class="font-bold text-gray-800">Your Courses:</h2>
                        <div class="ms-1">
                            @if (auth()->user()->courses->count() > 0)
                                {{ implode(', ', auth()->user()->courses->pluck('title')->toArray()) }}
                            @else
                                You are not enrolled in any courses.
                            @endif
                        </div>
                    </div>

                @endif
            </div>
            <div class="relative w-full">
                <div class="absolute right-0">
                    <div class="flex space-x-4">
                        @if (auth()->user()->is_admin)
                            <x-button red class="w-28 shadow" icon="cog" :href="route('admin')">
                                Admin
                            </x-button>
                        @endif
                        <div class="w-28 shadow">
                            <livewire:profile.logout-button />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
