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
        <h2 class="flex items-center space-x-2 overflow-auto text-xl font-semibold leading-tight">
            @foreach ($routes as $route)
                <div
                    class="{{ $route['title'] == 'Admin' ? 'text-red-500 hover:text-red-400' : 'text-slate-800 hover:text-slate-500 ' }}">
                    <a class="text-nowrap hover:underline" href="{{ $route['href'] }}" wire:navigate>
                        {{ __($route['title']) }}
                    </a>
                </div>
                @if (!$loop->last)
                    <x-icon name="chevron-right" class="h-5 min-h-5 w-5 min-w-5" solid />
                @endif
            @endforeach
        </h2>
    </x-slot>
</div>
