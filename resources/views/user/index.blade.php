@section('title', 'Users')

<x-app-layout>
    <livewire:layout.header :routes="[['title' => 'Admin', 'href' => route('admin')], ['title' => 'Users', 'href' => route('user.index')]]" />
    <x-slot:content>
        <livewire:user.all-users />
    </x-slot:content>
</x-app-layout>
