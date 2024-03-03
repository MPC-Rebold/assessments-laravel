@section('title', 'Admin')

<x-app-layout>
    <livewire:layout.header :routes="[['title' => 'Admin', 'href' => route('admin')]]" />
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <livewire:admin.sync />
            <hr>
            <livewire:admin.specification-setting />
            <livewire:admin.manage-students />
        </div>
    </div>
</x-app-layout>
