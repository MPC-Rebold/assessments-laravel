<?php

namespace App\Livewire\Admin;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use App\Models\Master;
use Illuminate\Database\Eloquent\Collection;
class CoursesStatus extends Component
{
    public Collection $masterCourses;

    public function mount(): void
    {
        $this->masterCourses = Master::all();
    }

    public function render(): View
    {
        return view('livewire.admin.courses-status');
    }
}
