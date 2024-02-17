@section('title', 'Dashboard')

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            <div class="space-y-4">
                <h1 class="text-2xl">{{ __('Your Courses') }}</h1>
                <hr class="border-2">
                <livewire:course.course-cards/>
            </div>
            <div class="space-y-4">
                <h1 class="text-2xl">{{ __('Upcoming Assessments') }}</h1>
                <hr class="border-2">
                <livewire:assessment.upcoming-assessments/>
            </div>

        </div>
    </div>
</x-app-layout>
