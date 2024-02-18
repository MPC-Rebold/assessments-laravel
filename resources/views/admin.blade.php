@section('title', 'Admin')

<x-app-layout>
    @livewire('layout.header', ['routes' => [
        ['title' => 'Admin', 'href' => route('admin')],
    ]])
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:admin.sync-canvas/>
                    <livewire:admin.sync-status/>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
