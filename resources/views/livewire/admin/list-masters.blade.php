<?php

use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Master;
use Livewire\Attributes\On;

new class extends Component {
    public Collection $masterCourses;

    #[On('syncUpdate')]
    #[On('createMaster')]
    public function updateMasters(): void
    {
        $this->mount();
    }

    public function mount(): void
    {
        $this->masterCourses = Master::all()
            ->load('courses')
            ->sortByDesc(function ($master) {
                return $master->courses->count();
            });
    }
}; ?>

<div>
    @if ($masterCourses->isEmpty())
        <div class="p-4 text-center">
            <p class="text-lg font-bold text-gray-400">
                No courses found
            </p>
        </div>
    @else
        @foreach ($masterCourses as $masterCourse)
            <livewire:admin.master-status :masterCourse="$masterCourse" key="{{ now() }}" />
            <hr />
        @endforeach
    @endif
    <livewire:master.create-master />
</div>
