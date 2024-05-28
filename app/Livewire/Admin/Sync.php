<?php

namespace App\Livewire\Admin;

use App\Services\SyncService;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class Sync extends Component
{
    use Actions;

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

        $this->dispatch('syncUpdate');

        $this->notification()->success(
            'Sync Complete',
        );
    }

    public function render(): View
    {
        return view('livewire.admin.sync');
    }
}
