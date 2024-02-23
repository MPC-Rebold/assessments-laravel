<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;

new class extends Component {
    public Collection $courses;

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function mount(): void
    {
        $this->courses = auth()->user()->courses->map(
            fn($course) => [
                'title' => $course->title,
                'href' => route('course.show', $course['id']),
            ],
        );
    }
}; ?>

<nav x-data="{ open: false }" class="sticky top-0 z-50 h-full border-b border-gray-100 bg-[#6f0834]">
    <!-- Primary Navigation Menu -->

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-14 justify-between">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="h-10" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden h-full space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>

                <div x-data="{ coursesOpen: false }" @click="coursesOpen = !coursesOpen" @click.outside="coursesOpen = false"
                    class="hidden h-full space-x-8 sm:ms-6 sm:flex">
                    <x-dropdown align="left">
                        <x-slot name="trigger" class="h-full">
                            <div class="h-full">
                                <x-nav-link :active="request()->routeIs('course.index', 'course.show', 'assessment')" class="group h-full">
                                    {{ __('Courses') }}
                                    <div :class="{ 'rotate-180': coursesOpen }" class="transition-all ease-in-out">
                                        <x-icon name="chevron-down"
                                            class="h-5 w-5 transition-all ease-in-out group-hover:scale-125" solid />
                                    </div>
                                </x-nav-link>
                            </div>
                        </x-slot>
                        @if (count($courses) > 0)
                            @for ($i = 0; $i < count($courses); $i++)
                                <x-dropdown.item class="group" :separator="(bool) $i" :href="$courses[$i]['href']" wire:navigate>
                                    <div class="flex w-full items-center justify-between">
                                        <div class="text-lg font-bold">
                                            {{ $courses[$i]['title'] }}
                                        </div>
                                        <x-icon name="chevron-right"
                                            class="h-5 transition-transform duration-300 group-hover:translate-x-1"
                                            solid />
                                    </div>
                                </x-dropdown.item>
                            @endfor
                        @else
                            <x-dropdown.item>
                                <div class="flex w-full items-center justify-between">
                                    <div class="text-lg font-bold">
                                        - No Courses -
                                    </div>
                                </div>
                            </x-dropdown.item>
                        @endif
                    </x-dropdown>
                </div>

                @if (auth()->user()->is_admin)
                    <div class="hidden h-full space-x-8 sm:-my-px sm:ms-6 sm:flex">
                        <x-nav-link :href="route('admin')" :active="request()->segment(1) == 'admin'" wire:navigate class="text-red-500"
                            :style="'danger'">
                            {{ __('ADMIN') }}
                        </x-nav-link>
                    </div>
                @endif
            </div>

            <!-- Settings Dropdown -->
            <div x-data="{ profileOpen: false }" @click="profileOpen = ! profileOpen" @click.outside="profileOpen = false"
                class="hidden sm:ms-6 sm:flex sm:items-center">
                <x-dropdown align="right" class="w-48">
                    <x-slot name="trigger">
                        <button
                            class="group inline-flex h-full items-center rounded-md border border-transparent bg-[#6f0834] px-3 py-2 text-sm font-medium leading-4 text-slate-200 transition duration-150 ease-in-out hover:text-white focus:outline-none">
                            <div>{{ auth()->user()->name }}</div>
                            <x-avatar sm :src="auth()->user()->avatar" class="ml-2" />
                            <div :class="{ 'rotate-180': profileOpen }" class="ms-1 transition-all ease-in-out">
                                <x-icon name="chevron-down"
                                    class="h-5 w-5 transition-all ease-in-out group-hover:scale-125" solid />
                            </div>
                        </button>
                    </x-slot>
                    @if (auth()->user()->is_admin)
                        <x-dropdown.item :href="route('admin')" wire:navigate>
                            <div class="flex items-center text-red-500">
                                <x-icon class="mr-2 h-5 w-5" name="cog" />
                                {{ __('Admin') }}
                            </div>
                        </x-dropdown.item>
                    @endif
                    <x-dropdown.item :href="route('profile')" wire:navigate icon="user">
                        {{ __('Profile') }}
                    </x-dropdown.item>

                    <x-dropdown.item wire:click="logout" icon="logout">
                        {{ __('Log Out') }}
                    </x-dropdown.item>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-md p-2 text-slate-200 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                    <div :class="{ 'hidden': open, 'inline-flex': !open }">
                        <x-icon name="menu" class="h-6" solid />
                    </div>
                    <div :class="{ 'hidden': !open, 'inline-flex': open }">
                        <x-icon name="x" class="h-6" solid />
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="absolute w-full bg-[#6f0834] pb-2">
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>
        <!-- Courses -->
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('course.index')" :active="request()->routeIs('course.index', 'course.show', 'assessment')" wire:navigate>
                {{ __('Courses') }}
            </x-responsive-nav-link>
        </div>
        @if (auth()->user()->is_admin)
            <div class="space-y-1 pb-3 pt-2">
                <x-responsive-nav-link :href="route('admin')" :active="request()->segment(1) == 'admin'" wire:navigate :style="'danger'">
                    {{ __('ADMIN') }}
                </x-responsive-nav-link>
            </div>
        @endif
        <!-- Responsive Settings Options -->
        <div class="border-t border-gray-200 pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-medium text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                    x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="text-sm font-medium text-gray-400">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate class="flex items-center space-x-1">
                    <x-icon name="user" class="h-5" />
                    <div>{{ __('Profile') }}</div>
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link class="flex items-center space-x-1">
                        <x-icon name="logout" class="h-5" />
                        <div>{{ __('Log Out') }}</div>
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
