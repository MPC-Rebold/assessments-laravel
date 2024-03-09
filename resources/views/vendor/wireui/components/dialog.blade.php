<div class="{{ $align }} {{ $zIndex }} fixed inset-0 flex items-end justify-center overflow-y-auto sm:pt-16"
    x-data="wireui_dialog({ id: '{{ $dialog }}' })" x-show="show" x-on:wireui:{{ $dialog }}.window="showDialog($event.detail)"
    x-on:wireui:confirm-{{ $dialog }}.window="confirmDialog($event.detail)"
    x-on:keydown.escape.window="handleEscape" style="display: none" x-cloak>
    <div class="{{ $dialog }}-backdrop @if ($blur) {{ $blur }} @endif fixed inset-0 transform bg-secondary-400 bg-opacity-60 transition-opacity dark:bg-secondary-700 dark:bg-opacity-60"
        x-show="show" x-on:click="dismiss" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
    </div>

    <div class="w-full p-4 transition-all sm:max-w-lg" x-show="show" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-on:mouseenter="pauseTimeout"
        x-on:mouseleave="resumeTimeout">
        <div class="relative space-y-4 rounded-xl bg-white p-4 shadow-md dark:bg-secondary-800"
            :class="{
                'sm:p-5 sm:pt-7': style === 'center',
                'sm:p-0 sm:pt-1': style === 'inline',
            }">
            <div class="absolute left-0 top-0 rounded-full bg-secondary-300 transition-all duration-150 ease-linear dark:bg-secondary-600"
                style="height: 2px; width: 100%;" x-ref="progressbar"
                x-show="dialog && dialog.progressbar && dialog.timeout">
            </div>

            <div x-show="dialog && dialog.closeButton" class="absolute -top-2 right-2">
                <button
                    class="{{ $dialog }}-button-close rounded-full p-1 text-secondary-300 focus:outline-none focus:ring-2 focus:ring-secondary-200"
                    x-on:click="close" type="button">
                    <span class="sr-only">close</span>
                    <x-dynamic-component :component="WireUi::component('icon')" class="h-5 w-5" name="x" />
                </button>
            </div>

            <div class="space-y-4"
                :class="{ 'sm:space-x-4 sm:flex sm:items-center sm:space-y-0 sm:px-5 sm:py-2': style === 'inline' }">
                <div class="mx-auto flex shrink-0 items-center justify-center self-start"
                    :class="{ 'sm:items-start sm:mx-0': style === 'inline' }" x-show="dialog && dialog.icon">
                    <div x-ref="iconContainer"></div>
                </div>

                <div class="mt-4 w-full" :class="{ 'sm:mt-5': style === 'center' }">
                    <h3 class="text-center text-lg font-medium leading-6 text-secondary-900 dark:text-secondary-400"
                        :class="{ 'sm:text-left': style === 'inline' }"
                        @unless ($title) x-ref="title" @endunless>
                        {{ $title }}
                    </h3>

                    <p class="mt-2 text-center text-sm text-secondary-500"
                        :class="{ 'sm:text-left': style === 'inline' }"
                        @unless ($description) x-ref="description" @endunless>
                        {{ $description }}
                    </p>

                    {{ $slot }}
                </div>
            </div>

            <div class="grid grid-cols-1 gap-y-2 rounded-b-xl sm:gap-x-3"
                :class="{
                    'sm:grid-cols-2 sm:gap-y-0': style === 'center',
                    'sm:p-4 sm:bg-secondary-100 sm:dark:bg-secondary-800 sm:grid-cols-none sm:flex sm:justify-end': style === 'inline',
                }"
                x-show="dialog && (dialog.accept || dialog.reject)">
                <div x-show="dialog && dialog.accept" class="sm:order-last" x-ref="accept"></div>
                <div x-show="dialog && dialog.reject" x-ref="reject"></div>
            </div>

            <div class="flex justify-center" x-show="dialog && dialog.close && !dialog.accept && !dialog.accept"
                x-ref="close">
            </div>
        </div>
    </div>
</div>
