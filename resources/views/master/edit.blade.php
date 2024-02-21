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

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <livewire:layout.section-header :header="$master->title" />
            <livewire:master.connected-courses :master="$master" />
        </div>
    </div>
</x-app-layout>
