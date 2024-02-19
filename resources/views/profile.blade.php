@section('title', 'Profile')

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex align-middle justify-between">
                    <div class="flex justify-between">
                        <x-avatar xl :src="auth()->user()->avatar" class="mx-auto"/>
                        <div class="ms-4">
                            <h1 class="text-xl font-bold text-gray-800">{{ auth()->user()->name }}</h1>
                            <p class="text-gray-600">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    @if(auth()->user()->is_admin)
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
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex">
                    @if(auth()->user()->is_admin)
                        <h2 class="font-bold text-gray-800">All Courses:</h2>
                        <div class="ms-1">
                            @if (\App\Models\Course::count() > 0)
                                {{  implode(', ', \App\Models\Course::pluck('title')->toArray()) }}
                            @else
                                There are no available courses. Try syncing with Canvas.
                            @endif
                        </div>
                    @else
                        <h2 class="font-bold text-gray-800">Courses:</h2>
                        <div class="ms-1">
                            @if (auth()->user()->courses->count() > 0)
                                {{  implode(', ', auth()->user()->courses->pluck('title')->toArray()) }}
                            @else
                                You are not enrolled in any courses.
                            @endif
                        </div>
                    @endif
                </div>
            </div>


        </div>
    </div>
</x-app-layout>
