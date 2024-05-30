<?php

namespace App\Livewire\Master;

use App\Models\Course;
use App\Models\Master;
use App\Services\SyncService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use WireUi\Traits\Actions;

class ConnectCourses extends Component
{
    use Actions;

    public Master $master;

    public array $connectedCourses;

    public array $availableCourses;

    public array $statusStrings;

    public Collection $connectedCourseModels;

    public Collection $missingCourses;

    public Collection $missingAssessments;

    public function mount(): void
    {
        $this->statusStrings = $this->master->statusStrings();
        $this->connectedCourseModels = $this->master->courses;
        $this->connectedCourses = $this->master->courses->pluck('title')->toArray();
        $this->availableCourses = Course::whereNull('master_id')
            ->orWhere('master_id', $this->master->id)
            ->get()
            ->pluck('title')
            ->toArray();

        $this->missingCourses = $this->master->status->missing_courses;
        $this->missingAssessments = $this->master->status->missing_assessments;
    }

    public function saveConnectedCourses(): void
    {
        try {
            SyncService::syncUpdateConnectedCourses($this->master, $this->connectedCourses);
        } catch (Exception $e) {
            $this->notification()->error('Saving course connections failed', $e->getMessage());

            return;
        }

        $this->mount();

        if (in_array(Master::OKAY, $this->master->statusStrings()) or
            in_array(Master::DISCONNECTED, $this->master->statusStrings())) {
            $this->notification()->success('Course connections saved');
        } else {
            $this->notification()->warning('Course connections saved with warnings');
        }

        $this->missingCourses = $this->master->status->missing_courses;
        $this->missingAssessments = $this->master->status->missing_assessments;

        $this->dispatch('refreshConnectedCourses', $this->missingCourses, $this->missingAssessments);
    }

    public function render(): View
    {
        return view('livewire.master.connect-courses');
    }
}
