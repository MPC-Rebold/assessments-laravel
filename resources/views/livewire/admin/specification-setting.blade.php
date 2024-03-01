<div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
    <div class="flex min-h-10 items-center justify-between space-x-4">
        <div>
            <div class="text-lg font-bold">
                Specification Grading
            </div>

        </div>
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-28">
                <x-select searchable="" clearable="" placeholder="Threshold" :options="['OFF', '65%', '70%', '75%', '80%', '85%', '90%', '95%']"
                    wire:model="specification_grading_threshold" />
            </div>

            <x-button disabled positive spinner class="min-w-28 bg-slate-300 hover:bg-slate-300"
                wire:dirty.attr.remove="disabled" wire:dirty.class.remove="bg-slate-300 hover:bg-slate-300"
                wire:click="openModal">
                Save
            </x-button>
            <x-modal wire:model.defer="modalOpen">
                <x-card title="Change Grading Option">
                    <div class='rounded-lg border border-warning-600 bg-warning-50 p-4'>
                        <div class="flex items-center border-b-2 border-negative-200 pb-3">
                            <x-icon name="exclamation" class="h-6 w-6 text-negative-600" />
                            <span class="ml-1 flex text-lg text-negative-600">
                                You are about to change the grading option for all courses
                            </span>
                        </div>
                        <div class="ml-5 mt-2 flex items-center justify-between pl-1">
                            <ul class="list-disc space-y-1 text-negative-600">
                                <li>This will affect <b>all assessments</b> on <b>all connected courses</b></li>
                                <li>Assessments already past due will not be affected</li>
                            </ul>
                        </div>
                    </div>
                    <x-slot name="footer">
                        <div class="flex justify-between">
                            <x-button flat label="Cancel" x-on:click="close" />
                            <x-button warning spinner label="Confirm" wire:click="updateSpecificationGrading" />
                        </div>
                    </x-slot>
                </x-card>
            </x-modal>
        </div>
    </div>
</div>
