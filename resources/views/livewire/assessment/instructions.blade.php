<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div x-data="{ open: false }" class="bg-slate-100 shadow sm:rounded-lg">
    <button @click="open = !open" class="group w-full bg-white p-4 shadow sm:rounded-lg sm:px-6 sm:py-4">
        <div class="flex items-center justify-between">
            <div class="flex flex-nowrap items-center">
                <x-icon name="information-circle" class="me-1 h-6 w-6 text-gray-500" />
                <h2 class="text-lg font-semibold group-hover:underline">
                    Instructions
                </h2>
            </div>
            <div :class="{ 'rotate-180': open }" class="transition-transform ease-in-out">
                <x-icon name="chevron-down" class="h-5 w-5 transition-all ease-in-out group-hover:scale-125" />
            </div>
        </div>
    </button>
    <div class="overflow-hidden transition-all duration-500" :class="{ 'max-h-0 invisible': !open, 'max-h-96': open }">
        <div class="px-6 py-4">
            <ul class="list-disc space-y-1 pl-5">
                <li>You have ten (10) guesses for each question.</li>
                <li>Feedback for your answer will be given after each attempt.</li>
                <li>Submit your work to upload your grade to Canvas.</li>
            </ul>
        </div>
    </div>
</div>
