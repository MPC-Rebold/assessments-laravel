<?php

use App\Models\User;

$user = User::find(request()->route('userId'));

?>

@section('title', 'Users - ' . $user->name)

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        ['title' => 'Users', 'href' => route('user.index')],
        ['title' => $user->name, 'href' => route('user.show', $user->id)],
    ]" />
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <livewire:user.show-info :user="$user" />
        </div>
    </div>
</x-app-layout>
