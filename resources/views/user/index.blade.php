@section('title', 'Users')

<x-app-layout>
    <livewire:layout.header :routes="[['title' => 'Admin', 'href' => route('admin')], ['title' => 'Users', 'href' => route('user.index')]]" />
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <livewire:user.all-users>
        </div>
    </div>
</x-app-layout>
