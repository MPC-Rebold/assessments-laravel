@section('title', 'Courses')

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Courses') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            <div class="space-y-4">
                @livewire('layout.section-header', ['header' => __('Your Courses')])
                <livewire:course.course-cards/>
            </div>
        </div>
    </div>
</x-app-layout>
