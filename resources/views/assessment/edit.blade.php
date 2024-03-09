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

    <livewire:assessment.questions-edit :assessment="$assessment" />
</x-app-layout>
