@section('title', 'Dashboard')

<x-app-layout>
    <livewire:layout.header :routes="[['title' => __('Dashboard'), 'href' => route('dashboard')]]" />
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-10 sm:px-6 lg:px-8">
            @if (!str_ends_with(auth()->user()->email, 'mpc.edu'))
                <x-warning>
                    <p>You are not logged in from an mpc.edu account</p>
                </x-warning>
            @endif
            <div class="space-y-4">
                <livewire:layout.section-header :header="__('Your Courses')" />
                <livewire:course.course-cards />
            </div>
            <div class="space-y-4">
                <livewire:layout.section-header :header="__('Upcoming Assessments')" />
                <livewire:assessment.upcoming-assessments />
            </div>
        </div>
    </div>
</x-app-layout>
