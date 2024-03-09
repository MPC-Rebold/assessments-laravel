<div x-data="wireui_datetime_picker({
    model: @entangleable($attributes->wire('model')),
})"
    x-props="{
        config: {
            interval: @toJs($interval),
            is12H:    @boolean($timeFormat == '12'),
            readonly: @boolean($readonly),
            disabled: @boolean($disabled),
            min: @toJs($min ? $min->format('Y-m-d\TH:i') : null),
            max: @toJs($max ? $max->format('Y-m-d\TH:i') : null),
            minTime: @toJs($minTime),
            maxTime: @toJs($maxTime),
        },
        withoutTimezone: @boolean($withoutTimezone),
        timezone:      @toJs($timezone),
        userTimezone:  @toJs($userTimezone ?? ''),
        parseFormat:   @toJs($parseFormat ?? ''),
        displayFormat: @toJs($displayFormat ?? ''),
        weekDays:      @lang('wireui::messages.datePicker.days'),
        monthNames:    @lang('wireui::messages.datePicker.months'),
        withoutTime:   @boolean($withoutTime),
    }"
    {{ $attributes->only('wire:key')->class('relative w-full')->merge(['wire:key' => "datepicker::{$name}"]) }}>
    <x-dynamic-component :component="WireUi::component('input')"
        {{ $attributes->whereDoesntStartWith(['wire:model', 'x-model', 'wire:key', 'readonly']) }} :borderless="$borderless"
        :shadowless="$shadowless" :label="$label" :hint="$hint" :corner-hint="$cornerHint" :icon="$icon" :prefix="$prefix"
        :prepend="$prepend" readonly x-on:click="toggle" x-bind:value="model ? getDisplayValue() : null">
        @if (!$readonly && !$disabled)
            <x-slot name="append">
                <div class="z-5 absolute inset-y-0 right-3 flex items-center justify-center">
                    <div
                        class="{{ $errors->has($name) ? 'text-negative-400 dark:text-negative-600' : 'text-secondary-400' }} my-auto flex items-center gap-x-2">

                        @if ($clearable)
                            <x-dynamic-component :component="WireUi::component('icon')"
                                class="h-4 w-4 cursor-pointer transition-colors duration-150 ease-in-out hover:text-negative-500"
                                x-cloak name="x" x-show="model" x-on:click="clearDate()" />
                        @endif

                        <x-dynamic-component :component="WireUi::component('icon')" class="h-5 w-5 cursor-pointer" :name="$rightIcon"
                            x-on:click="toggle" />
                    </div>
                </div>
            </x-slot>
        @endif
    </x-dynamic-component>

    <x-wireui::parts.popover :margin="(bool) $label" root-class="sm:!w-72 ml-auto"
        class="max-h-96 overflow-y-auto p-3 sm:w-72">
        <div x-show="tab === 'date'" class="space-y-5">
            @unless ($withoutTips)
                <div class="grid grid-cols-3 gap-x-2 text-center text-secondary-600">
                    <x-dynamic-component :component="WireUi::component('button')" class="border-none bg-secondary-100 dark:bg-secondary-800"
                        x-on:click="selectYesterday" :label="__('wireui::messages.datePicker.yesterday')" />

                    <x-dynamic-component :component="WireUi::component('button')" class="border-none bg-secondary-100 dark:bg-secondary-800"
                        x-on:click="selectToday" :label="__('wireui::messages.datePicker.today')" />

                    <x-dynamic-component :component="WireUi::component('button')" class="border-none bg-secondary-100 dark:bg-secondary-800"
                        x-on:click="selectTomorrow" :label="__('wireui::messages.datePicker.tomorrow')" />
                </div>
            @endunless

            <div class="flex items-center justify-between">
                <x-dynamic-component :component="WireUi::component('button')" class="shrink-0 rounded-lg" x-show="!monthsPicker"
                    x-on:click="previousMonth" icon="chevron-left" flat />

                <div class="flex w-full items-center justify-center gap-x-2 text-secondary-600 dark:text-secondary-500">
                    <button class="focus:underline focus:outline-none" x-text="monthNames[month]"
                        x-on:click="monthsPicker = !monthsPicker" type="button">
                    </button>
                    <input
                        class="w-14 appearance-none border-none p-0 ring-0 focus:outline-none focus:ring-0 dark:bg-secondary-800"
                        x-model="year" x-on:input.debounce.500ms="fillPickerDates" type="number" />
                </div>

                <x-dynamic-component :component="WireUi::component('button')" class="shrink-0 rounded-lg" x-show="!monthsPicker"
                    x-on:click="nextMonth" icon="chevron-right" flat />
            </div>

            <div class="relative">
                <div class="absolute inset-0 grid grid-cols-3 gap-3 bg-white dark:bg-secondary-800"
                    x-show="monthsPicker" x-transition>
                    <template x-for="(monthName, index) in monthNames" :key="`month.${monthName}`">
                        <x-dynamic-component :component="WireUi::component('button')"
                            class="uppercase text-secondary-400 dark:border-0 dark:hover:bg-secondary-700"
                            x-on:click="selectMonth(index)" x-text="monthName" xs />
                    </template>
                </div>

                <div class="grid grid-cols-7 gap-2">
                    <template x-for="day in weekDays" :key="`week-day.${day}`">
                        <span class="pointer-events-none text-center text-3xs uppercase text-secondary-400"
                            x-text="day">
                        </span>
                    </template>

                    <template x-for="date in dates" :key="`date.${date.day}.${date.month}`">
                        <div class="picker-days flex justify-center">
                            <button
                                class="focus:ring-ofsset-2 h-6 w-7 rounded-md text-sm hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-600 disabled:cursor-not-allowed dark:hover:bg-secondary-700 dark:focus:ring-secondary-400"
                                :class="{
                                    'text-secondary-600 dark:text-secondary-400': !date.isDisabled && !date
                                        .isSelected && date.month === month,
                                    'text-secondary-400 dark:text-secondary-600': date.isDisabled || date.month !==
                                        month,
                                    'text-primary-600 border border-primary-600 dark:border-gray-400': date.isToday && !
                                        date.isSelected,
                                    'disabled:text-primary-400 disabled:border-primary-400': date.isToday && !date
                                        .isSelected,
                                    '!text-white bg-primary-600 font-semibold border border-primary-600': date
                                        .isSelected,
                                    'disabled:bg-primary-400 disabled:border-primary-400': date.isSelected,
                                    'hover:bg-primary-600 dark:bg-secondary-700 dark:border-secondary-400': date
                                        .isSelected,
                                }"
                                :disabled="date.isDisabled" x-on:click="selectDate(date)" x-text="date.day"
                                type="button">
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div x-show="tab === 'time'" x-transition>
            <x-dynamic-component :component="WireUi::component('input')" id="search.{{ $attributes->wire('model')->value() }}"
                :label="__('wireui::messages.selectTime')" x-model="searchTime" x-bind:placeholder="getSearchPlaceholder" x-ref="searchTime"
                x-on:input.debounce.150ms="onSearchTime($event.target.value)" />

            <div x-ref="timesContainer"
                class="picker-times mt-1 flex max-h-52 w-full flex-col overflow-y-auto pb-1 pt-2">
                <template x-for="time in filteredTimes" :key="time.value">
                    <button
                        class="group relative cursor-pointer select-none rounded-md py-2 pl-2 pr-9 text-left transition-colors duration-100 ease-in-out hover:bg-primary-600 hover:text-white focus:bg-primary-100 focus:outline-none dark:text-secondary-400 dark:hover:bg-secondary-700 dark:focus:bg-secondary-700"
                        :class="{
                            'text-primary-600': modelTime === time.value,
                            'text-secondary-700': modelTime !== time.value,
                        }"
                        :name="`times.${time.value}`" type="button" x-on:click="selectTime(time)">
                        <span x-text="time.label"></span>
                        <span
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-primary-600 group-hover:text-white dark:text-secondary-400"
                            x-show="modelTime === time.value">
                            <x-dynamic-component :component="WireUi::component('icon')" name="check" class="h-5 w-5" />
                        </span>
                    </button>
                </template>
            </div>
        </div>
    </x-wireui::parts.popover>
</div>
