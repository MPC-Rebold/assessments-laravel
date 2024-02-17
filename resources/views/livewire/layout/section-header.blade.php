<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public string $header;
}; ?>


<div class="space-y-4 sm: px-2">
    <h1 class="text-2xl">{{ $header }}</h1>
    <hr class="border-2">
</div>