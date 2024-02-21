<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public string $header;
}; ?>

<div class="sm: space-y-4 px-2">
    <h1 class="text-2xl">{{ $header }}</h1>
    <hr class="border-2">
</div>
