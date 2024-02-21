<div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
    <div class="flex justify-between items-center min-h-10 space-x-4">
        <div>
            <div class="text-lg font-bold ">
                Specification Grading
            </div>
            <div class="text-gray-500">
                @if($specification_grading)
                    On | Threshold: {{$specification_grading_threshold}}
                @else
                    Off
                @endif
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-28">
                <x-select
                        searchable=""
                        clearable=""
                        placeholder="Threshold"
                        :options="['OFF', '65%', '70%', '75%', '80%', '85%', '90%', '95%']"
                        wire:model="specification_grading_threshold"
                />
            </div>

            <x-button
                    disabled
                    positive
                    spinner
                    class="min-w-28 bg-slate-300 hover:bg-slate-300"
                    wire:dirty.attr.remove="disabled"
                    wire:dirty.class.remove="bg-slate-300 hover:bg-slate-300"
                    wire:click="updateSpecificationGrading">
                Save
            </x-button>

        </div>
    </div>
</div>