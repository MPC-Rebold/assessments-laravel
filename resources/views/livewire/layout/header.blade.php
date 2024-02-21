<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * @var array $routes
     *  array of
     *     [
     *        'title' => string,
     *        'href' => string,
     *     ],
     */
    public array $routes;
}; ?>

<div>
    <x-slot name="header">
        <h2 class="flex items-center space-x-2 text-xl font-semibold leading-tight text-gray-800">
            @foreach ($routes as $route)
                <a class="hover:text-slate-500 hover:underline" href="{{ $route['href'] }}" wire:navigate>
                    {{ __($route['title']) }}
                </a>
                @if (!$loop->last)
                    <x-icon name="chevron-right" class="h-5" solid />
                @endif
            @endforeach
        </h2>
    </x-slot>
</div>
