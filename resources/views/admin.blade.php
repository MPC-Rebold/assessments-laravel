@section('title', 'Admin')

<x-app-layout>
    <livewire:layout.header :routes="[['title' => 'Admin', 'href' => route('admin')]]" />
    <x-slot:content>
        <livewire:admin.sync />
        <hr>
        <livewire:admin.specification-setting />
        <livewire:admin.manage-students />
    </x-slot:content>
</x-app-layout>
