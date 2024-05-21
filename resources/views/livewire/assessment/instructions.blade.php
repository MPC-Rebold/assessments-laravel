<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div x-data="{ open: false }" class="bg-slate-100 shadow sm:rounded-lg">
    <button @click="open = !open" class="group w-full bg-white p-4 shadow sm:rounded-lg sm:px-6 sm:py-4">
        <div class="flex items-center justify-between">
            <div class="flex flex-nowrap items-center">
                <x-icon name="information-circle" class="me-1 h-6 w-6 text-slate-800" />
                <h2
                    class="-mt-0.5 text-lg font-semibold text-slate-800 group-hover:text-slate-500 group-hover:underline">
                    Instructions
                </h2>
            </div>
            <div :class="{ 'rotate-180': open }" class="transition-transform ease-in-out">
                <x-icon name="chevron-down" class="h-5 w-5 transition-all ease-in-out group-hover:scale-125" />
            </div>
        </div>
    </button>
    <div class="overflow-hidden transition-all duration-500"
        :class="{ 'max-h-0 invisible': !open, 'max-h-[100vh]': open }">
        <div class="px-6 py-4">
            <ul class="list-disc space-y-1 pl-5">
                <li>You have ten (10) guesses for each question</li>
                <li>Feedback for your answer will be given after each attempt</li>
                <ul class="list-disc pl-1 sm:list-inside">
                    <li>Correct characters will be green</li>
                    <li>Additional incorrect characters will have a strikethrough</li>
                    <li>Missing characters will be marked as underscores</li>
                    <li>
                        Example: if the correct answer is <b>example</b>
                        and you entered <b>exa123pl</b>
                        <div
                            class="mt-1 w-fit overflow-auto text-nowrap rounded-md bg-slate-200 px-2 py-1 tracking-widest">
                            <keep__>e</keep__>
                            <keep__>x</keep__>
                            <keep__>a</keep__>
                            <delete__>1</delete__>
                            <delete__>2</delete__>
                            <delete__>3</delete__>
                            _
                            <keep__>p</keep__>
                            <keep__>l</keep__>
                            _
                        </div>
                    </li>
                </ul>
                <br>
                <li>
                    <div class="flex flex-wrap items-center">
                        <div> To upload your grade to Canvas press
                        </div>
                        <div
                            class="mx-1 w-fit min-w-fit overflow-auto text-nowrap rounded-md bg-positive-500 px-2 py-1 text-white"
                        x-on:click="document.getElementById('submit_to_canvas').scrollIntoView({behavior: 'smooth'})">
                            Submit to Canvas
                        </div>
                        <div>
                        </div>
                    </div>
                </li>
                <ul class="list-disc pl-1 sm:list-inside">
                    <li>You can submit multiple times up to the due date</li>
                </ul>
            </ul>
        </div>
    </div>
</div>
