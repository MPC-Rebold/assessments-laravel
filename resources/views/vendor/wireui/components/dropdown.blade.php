<div class="relative inline-block text-left" x-data="wireui_dropdown" x-on:click.outside="close"
    x-on:keydown.escape.window="close" {{ $attributes->only('wire:key') }}>
    <div class="h-full cursor-pointer focus:outline-none" x-on:click="toggle">
        @if (isset($trigger))
            {{ $trigger }}
        @else
            <x-dynamic-component :component="WireUi::component('icon')"
                class="h-4 w-4 text-secondary-500 transition duration-150 ease-in-out hover:text-secondary-700 dark:hover:text-secondary-600"
                name="dots-vertical" />
        @endif
    </div>

    <div x-show="status" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        {{ $attributes->except('wire:key')->class([$getAlign(), $width, 'z-30 absolute mt-2 whitespace-nowrap']) }}
        style="display: none;" @unless ($persistent) x-on:click="close" @endunless>
        <div
            class="{{ $height }} relative overflow-auto rounded-lg border border-secondary-200 bg-white p-1 shadow-lg soft-scrollbar dark:border-secondary-600 dark:bg-secondary-800">
            {{ $slot }}
        </div>
    </div>
</div>
