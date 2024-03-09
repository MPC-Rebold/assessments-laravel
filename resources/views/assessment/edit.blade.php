<?php

use App\Models\Master;
use App\Models\Assessment;

$master = Master::find(request()->route('masterId'));
$assessment = Assessment::find(request()->route('assessmentId'));

if (!$master || !$assessment) {
    abort(404);
}

?>

@section('title', 'Edit ' . $assessment->title)

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        ['title' => $master->title, 'href' => route('master.edit', $master->id)],
        ['title' => $assessment->title, 'href' => route('assessment.show', [$master->id, $assessment->id])],
    ]" />

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <livewire:layout.section-header header="Edit {{ $assessment->title }}" />
        </div>
    </div>
</x-app-layout>
