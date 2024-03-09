<?php

use App\Models\Master;

$master = Master::find(request()->route('masterId'));

if (!$master) {
    abort(404);
}

?>

@section('title', $master->title)

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        ['title' => $master->title, 'href' => route('master.edit', $master->id)],
    ]" />

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <livewire:layout.section-header :header="$master->title" />
            <livewire:master.connect-courses :master="$master" />
            <hr>
            <livewire:master.list-assessments :master="$master" />
        </div>
    </div>
</x-app-layout>
