<div class="space-y-4">
    <div class="space-y-4">
        @if (in_array('NoSeed', $statusStrings))
            <livewire:master.status-no-seed :master="$master" />
        @endif
        @if (in_array('Warning', $statusStrings))
            <livewire:master.status-warning :missingCourses="$missingCourses" :missingAssessments="$missingAssessments" />
        @endif
        @if (in_array('Disconnected', $statusStrings))
            <livewire:master.status-disconnected />
        @endif
        @if (in_array('Okay', $statusStrings))
            <livewire:master.status-successful />
        @endif

    </div>
    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
        <form>
            <div class="flex flex-wrap items-center justify-between gap-x-16 gap-y-4 sm:flex-nowrap">
                <h2 class="min-w-44 text-lg font-bold text-gray-800">
                    Connected Courses
                </h2>
                <div class="flex w-full items-center justify-end gap-4">
                    <x-select multiselect searchable class="max-w-md" wire:model="connectedCourses"
                        placeholder="No connected courses" :options="$availableCourses" />

                    <x-button disabled positive spinner class="min-w-24 bg-slate-300 hover:bg-slate-300"
                        wire:dirty.attr.remove="disabled" wire:dirty.class.remove="bg-slate-300 hover:bg-slate-300"
                        wire:click="saveConnectedCourses">
                        Save
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</div>
