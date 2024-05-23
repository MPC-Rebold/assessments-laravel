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

    <livewire:master.edit :master="$master" />
</x-app-layout>
