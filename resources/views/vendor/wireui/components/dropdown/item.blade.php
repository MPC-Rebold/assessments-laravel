@if ($separator)
    <div class="my-1 w-full border-t border-secondary-200 dark:border-secondary-600"></div>
@endif

<a {{ $attributes->merge(['class' => $getClasses()]) }}>
    @if ($icon)
        <x-dynamic-component :component="WireUi::component('icon')" :name="$icon" class="mr-2 h-5 w-5" />
    @endif

    {{ $label ?? $slot }}
</a>
