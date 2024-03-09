<div x-data="wireui_inputs_password" {{ $attributes->only('wire:key')->class('w-full') }}>
    <x-dynamic-component :component="WireUi::component('input')" {{ $attributes->except('wire:key') }} :borderless="$borderless" :shadowless="$shadowless"
        :label="$label" :hint="$hint" :corner-hint="$cornerHint" :icon="$icon" :prefix="$prefix" :prepend="$prepend"
        x-bind:type="type" type="password">
        <x-slot name="append">
            <div class="absolute inset-y-0 right-0 flex items-center pr-2.5">
                <div x-on:click="toggle" class="cursor-pointer text-gray-400">
                    <x-dynamic-component x-show="!status" :component="WireUi::component('icon')" name="eye-off" class="h-5 w-5" />
                    <x-dynamic-component x-show="status" :component="WireUi::component('icon')" name="eye" class="h-5 w-5" />
                </div>
            </div>
        </x-slot>
    </x-dynamic-component>
</div>
