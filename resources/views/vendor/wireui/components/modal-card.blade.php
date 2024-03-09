<x-dynamic-component :component="WireUi::component('modal')" {{ $attributes }} :spacing="$fullscreen ? '' : $spacing" :z-index="$zIndex" :max-width="$maxWidth"
    :align="$align" :blur="$blur">
    <x-dynamic-component :component="WireUi::component('card')" :title="$title" :rounded="$squared || $fullscreen ? '' : $rounded" :card-classes="$fullscreen ? 'min-h-screen' : ''" :shadow="$shadow"
        :padding="$padding" :divider="$divider">
        @if ($header)
            <x-slot name="header">
                {{ $header }}
            </x-slot>
        @elseif(!$hideClose)
            <x-slot name="action">
                <button
                    class="rounded-full p-1 text-secondary-300 focus:outline-none focus:ring-2 focus:ring-secondary-200"
                    x-on:click="close" tabindex="-1">
                    <x-dynamic-component :component="WireUi::component('icon')" name="x" class="h-5 w-5" />
                </button>
            </x-slot>
        @endif

        {{ $slot }}

        @isset($footer)
            <x-slot name="footer">
                {{ $footer }}
            </x-slot>
        @endisset
    </x-dynamic-component>
</x-dynamic-component>
