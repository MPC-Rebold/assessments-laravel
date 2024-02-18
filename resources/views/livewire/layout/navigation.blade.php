<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function with(): array
    {
        return [
            'courses' => auth()->user()->courses->map(fn($course) => [
                'title' => $course->title,
                'href' => route('course', $course['id']),
            ]),
        ];
    }
}; ?>

<nav x-data="{open: false}" class="bg-[#6f0834] border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-10 w-full fill-current text-gray-800"/>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex h-full">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                                wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>

                <div x-data="{coursesOpen: false}" @click="coursesOpen = !coursesOpen"
                     @click.outside="coursesOpen = false"
                     class="hidden space-x-8 sm:-my-px sm:ms-6 sm:flex h-full">
                    <x-dropdown align="left">
                        <x-slot name="trigger" class="h-full">
                            <div class="hidden space-x-8 sm:-my-px sm:flex h-full">
                                <x-nav-link :active="request()->routeIs('courses', 'course', 'assessment')"
                                            class="h-full">
                                    {{ __('Courses') }}
                                    <div :class="{'rotate-180': coursesOpen}" class="transition-all ease-in-out">
                                        <x-icon name="chevron-down" class="h-5" solid/>
                                    </div>
                                </x-nav-link>
                            </div>
                        </x-slot>
                        @for($i = 0; $i < count($courses); $i++)
                            <x-dropdown.item class="group" :separator="(bool)$i" :href="$courses[$i]['href']"
                                             wire:navigate>
                                <div class="flex justify-between items-center w-full">
                                    <div class="font-bold text-lg">
                                        {{ $courses[$i]['title'] }}
                                    </div>
                                    <x-icon name="chevron-right"
                                            class="h-5 transition-transform duration-300 group-hover:translate-x-1"
                                            solid/>
                                </div>
                            </x-dropdown.item>
                        @endfor
                    </x-dropdown>
                </div>

                @if(auth()->user()->is_admin)
                    <div class="hidden space-x-8 sm:-my-px sm:ms-6 sm:flex h-full">
                        <x-nav-link :href="route('admin')" :active="request()->routeIs('admin')" wire:navigate
                                    class="text-red-500" :style="'danger'">
                            {{ __('ADMIN') }}
                        </x-nav-link>
                    </div>
                @endif
            </div>

            <!-- Settings Dropdown -->
            <div x-data="{profileOpen: false}" @click="profileOpen = ! profileOpen" @click.outside="profileOpen = false"
                 class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" class="w-48">
                    <x-slot name="trigger">
                        <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-slate-200 bg-[#6f0834] hover:text-white focus:outline-none transition ease-in-out duration-150">
                            <div>{{ auth()->user()->name }}</div>
                            <x-avatar sm :src="auth()->user()->avatar" class="ml-2"/>
                            <div :class="{'rotate-180': profileOpen}" class="ms-1 transition-all ease-in-out">
                                <x-icon name="chevron-down" class="h-5" solid/>
                            </div>
                        </button>
                    </x-slot>
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
                        class="inline-flex items-center justify-center p-2 rounded-md text-slate-200 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <div :class="{'hidden': open, 'inline-flex': ! open }">
                        <x-icon name="menu" class="h-6" solid/>
                    </div>
                    <div :class="{'hidden': ! open, 'inline-flex': open }">
                        <x-icon name="x" class="h-6" solid/>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>
        <!-- Courses -->
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('courses')"
                                   :active="request()->routeIs('courses', 'course', 'assessment')" wire:navigate>
                {{ __('Courses') }}
            </x-responsive-nav-link>
        </div>
        @if (auth()->user()->is_admin)
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('admin')" :active="request()->routeIs('admin')" wire:navigate
                                       :style="'danger'">
                    {{ __('ADMIN') }}
                </x-responsive-nav-link>
            </div>
        @endif
        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-200"
                     x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                     x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-400">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate class="flex space-x-1 items-center">
                    <x-icon name="user" class="h-5"/>
                    <div>{{ __('Profile') }}</div>
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link class="flex space-x-1 items-center">
                        <x-icon name="logout" class="h-5"/>
                        <div>{{ __('Log Out') }}</div>
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
