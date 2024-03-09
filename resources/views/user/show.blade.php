<?php

use App\Models\User;

$user = User::find(request()->route('userId'));

if (!$user) {
    abort(404);
}
?>

@section('title', 'Users - ' . $user->name)

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        ['title' => 'Users', 'href' => route('user.index')],
        ['title' => $user->name, 'href' => route('user.show', $user->id)],
    ]" />
    <x-slot:content>
        <livewire:user.show-info :user="$user" />
    </x-slot:content>
</x-app-layout>
