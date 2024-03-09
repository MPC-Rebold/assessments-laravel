@php
    $hasError = !$errorless && $name && $errors->has($name);
@endphp

<div class="@if ($disabled) opacity-60 @endif w-full">
    @if ($label || $cornerHint)
        <div class="{{ !$label && $cornerHint ? 'justify-end' : 'justify-between items-end' }} mb-1 flex">
            @if ($label)
                <x-dynamic-component :component="WireUi::component('label')" :label="$label" :has-error="$hasError" :for="$id" />
            @endif

            @if ($cornerHint)
                <x-dynamic-component :component="WireUi::component('label')" :label="$cornerHint" :has-error="$hasError" :for="$id" />
            @endif
        </div>
    @endif

    <div class="@unless ($shadowless) shadow-sm @endunless relative rounded-md">
        @if ($prefix || $icon)
            <div
                class="{{ $hasError ? 'text-negative-500' : 'text-secondary-400' }} pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                @if ($icon)
                    <x-dynamic-component :component="WireUi::component('icon')" :name="$icon" class="h-5 w-5" />
                @elseif($prefix)
                    <span class="flex items-center self-center pl-1">
                        {{ $prefix }}
                    </span>
                @endif
            </div>
        @elseif($prepend)
            {{ $prepend }}
        @endif

        <input
            {{ $attributes->class([$getInputClasses($hasError)])->merge([
                'type' => 'text',
                'autocomplete' => 'off',
            ]) }} />

        @if ($suffix || $rightIcon || ($hasError && !$append))
            <div
                class="{{ $hasError ? 'text-negative-500' : 'text-secondary-400' }} pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                @if ($rightIcon)
                    <x-dynamic-component :component="WireUi::component('icon')" :name="$rightIcon" class="h-5 w-5" />
                @elseif ($suffix)
                    <span class="flex items-center justify-center pr-1">
                        {{ $suffix }}
                    </span>
                @elseif ($hasError)
                    <x-dynamic-component :component="WireUi::component('icon')" name="exclamation-circle" class="h-5 w-5" />
                @endif
            </div>
        @elseif ($append)
            {{ $append }}
        @endif
    </div>

    @if (!$hasError && $hint)
        <label @if ($id) for="{{ $id }}" @endif
            class="mt-2 text-sm text-secondary-500 dark:text-secondary-400">
            {{ $hint }}
        </label>
    @endif

    @if ($name && !$errorless)
        <x-dynamic-component :component="WireUi::component('error')" :name="$name" />
    @endif
</div>
