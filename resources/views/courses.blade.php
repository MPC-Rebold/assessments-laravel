@section('title', 'Courses')

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Courses') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-10 sm:px-6 lg:px-8">
            <div class="space-y-4">
                @livewire('layout.section-header', ['header' => __('Your Courses')])
                <livewire:course.course-cards />
            </div>
        </div>
    </div>
</x-app-layout>
