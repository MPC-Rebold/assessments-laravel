<?php

namespace App\Livewire\Admin;

use App\Exceptions\UserException;
use App\Services\SyncService;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class Sync extends Component
{
    use Actions;

    /**
     * @throws Exception if sync fails
     */
    public function sync(): void
    {

        try {
            SyncService::sync();
        } catch (Exception $e) {
            if (is_a($e, UserException::class)) {
                $this->notification()->error('Sync Failed', $e->getMessage());

                return;
            }
            throw $e;
        }

        $this->dispatch('syncUpdate');

        $this->notification()->success('Sync Complete');
    }

    public function render(): View
    {
        return view('livewire.admin.sync');
    }
}
