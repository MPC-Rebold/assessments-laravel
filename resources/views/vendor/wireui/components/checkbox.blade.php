<div>
    <label for="{{ $id }}" class="{{ $errors->has($name) ? 'text-negative-600' : '' }} flex items-center">
        <div class="relative flex items-start">
            @if ($leftLabel)
                <div class="mr-2 text-right text-sm">
                    <x-dynamic-component :component="WireUi::component('label')" class="" :for="$id" :label="$leftLabel"
                        :has-error="$errors->has($name)" />
                    @if ($description)
                        <div class="text-gray-500">{{ $description }}</div>
                    @endif
                </div>
            @endif

            <div class="flex h-5 items-center">
                <input
                    {{ $attributes->class([$getClasses($errors->has($name))])->merge([
                        'type' => 'checkbox',
                    ]) }} />
            </div>

            @if ($label)
                <div class="ml-2 text-sm">
                    <x-dynamic-component :component="WireUi::component('label')" class="" :for="$id" :label="$label"
                        :has-error="$errors->has($name)" />
                    @if ($description)
                        <div id="{{ $id }} . comments-description" class="text-gray-500">{{ $description }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </label>

    @if ($name)
        <x-dynamic-component :component="WireUi::component('error')" :name="$name" />
    @endif
</div>
