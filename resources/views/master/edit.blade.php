<?php

use App\Models\Master;

$master = Master::find(last(request()->segments()));

?>

@section('title', $master->title)

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        ['title' => $master->title, 'href' => route('master.edit', $master->id)],
    ]" />

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="sm: space-y-4 px-2">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl">{{ $master->title }}</h1>
                    <div class="flex items-center space-x-4">
                        <div class="text-gray-500">
                            Status
                        </div>
                        <x-button slate icon="ban">
                            Disconnected
                        </x-button>
                    </div>
                </div>
                <hr class="border-2">
            </div>
            <livewire:master.connected-courses :master="$master" />
        </div>
    </div>
</x-app-layout>
