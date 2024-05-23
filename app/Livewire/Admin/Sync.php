<?php

namespace App\Livewire\Admin;

use App\Models\Master;
use App\Services\SeedService;
use App\Services\SyncService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;
use WireUi\Traits\Actions;

class Sync extends Component
{
    use Actions;

    public Collection $masterCourses;

    #[Validate('required|string|max:50')]
    public string $newMasterTitle = '';

    public bool $showInput = false;

    public function saveNewMaster(): void
    {
        $this->validate();

        try {
            SeedService::createMaster($this->newMasterTitle);
        } catch (Exception $e) {
            $this->notification()->error(
                'Failed to create master',
                $e->getMessage(),
            );

            return;
        }

        $this->notification()->success(
            'Master created',
            $this->newMasterTitle,
        );

        $this->newMasterTitle = '';
        $this->showInput = false;
        $this->mount();
    }

    public function sync(): void
    {

        try {
            SyncService::sync();
        } catch (Exception $e) {
            $this->notification()->error(
                'Sync Failed',
                $e->getMessage(),
            );

            return;
        }

        $this->mount();
        $this->dispatch('updateLastSyncedAt');

        $this->notification()->success(
            'Sync Complete',
        );
    }

    public function mount(): void
    {
        $this->masterCourses = Master::all()->load('courses')->
            sortByDesc(function ($master) {
                return $master->courses->count();
            });
    }

    public function render(): View
    {
        return view('livewire.admin.sync');
    }
}
