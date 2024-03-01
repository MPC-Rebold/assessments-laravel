<?php

namespace App\Livewire\Master;

use App\Livewire\Admin\Sync;
use App\Models\Course;
use App\Models\Master;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\Actions;

class ConnectCourses extends Component
{
    use Actions;

    public Master $master;

    public array $connectedCourses;

    public array $availableCourses;

    public array $statusStrings;

    public Collection $missingCourses;

    public Collection $missingAssessments;

    #[On(('refresh'))]
    public function mount(): void
    {
        $this->statusStrings = $this->master->statusStrings();
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
            DB::beginTransaction();

            $this->master->courses()->update(['master_id' => null]);

            $courses = Course::whereIn('title', $this->connectedCourses);
            $courses->update(['master_id' => $this->master->id]);

            $users = User::all();
            foreach ($users as $user) {
                $user->connectCourses();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->notification()->error('Course connections failed with error ' . $e->getMessage());

            return;
        }

        $sync = new Sync();
        $sync->sync();

        $this->mount();

        if (in_array('Okay', $this->master->statusStrings()) or
            in_array('Disconnected', $this->master->statusStrings())) {
            $this->notification()->success('Course connections saved');
        } else {
            $this->notification()->error('Course connections saved with warnings');
        }
    }

    public function render(): View
    {
        return view('livewire.master.connect-courses');
    }
}
