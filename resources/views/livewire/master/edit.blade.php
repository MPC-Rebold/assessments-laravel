<?php

use App\Models\Master;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public Master $master;

    public function mount(Master $master): void
    {
        $this->master = $master;
    }

    #[On('updateMaster')]
    public function updateMaster(int $masterId): void
    {
        $this->mount(Master::find($masterId));
    }
}; ?>

<div class="py-10">
    <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <livewire:layout.section-header :header="$master->title" />
        <livewire:master.connect-courses :master="$master" />
        <hr>
        <livewire:master.list-assessments :master="$master" />
        <hr>
        <livewire:master.rename-master :master="$master" />
        <livewire:master.download-master :master="$master" />
        <livewire:master.delete-master :master="$master" />
    </div>
</div>
