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
        $this->dispatch('updateStatus');
    }
}; ?>

<div class="space-y-6">
    <livewire:layout.section-header :header="$master->title" />
    <livewire:master.connect-courses :master="$master" />
    <livewire:master.list-assessments :master="$master" />
    <hr>
    <livewire:master.rename-master :master="$master" />
    <livewire:master.download-master :master="$master" />
    <livewire:master.delete-master :master="$master" />
</div>
