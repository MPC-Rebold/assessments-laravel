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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center space-x-2">
            @for($i = 0; $i < count($routes); $i++)
                @if($i < count($routes) - 1)
                    <a class="hover:text-slate-500 hover:underline" href="{{$routes[$i]["href"]}}" wire:navigate>
                        {{ __($routes[$i]["title"]) }}
                    </a>
                    <x-icon name="chevron-right" class="h-5" solid/>
                @else
                    <a class="select-none">
                        {{ __($routes[$i]["title"]) }}
                    </a>
                @endif
            @endfor
        </h2>
    </x-slot>
</div>