<?php

use Livewire\Volt\Component;
use App\Services\CanvasService;


new class extends Component {
    public function with(): array
    {
        $canvas = new CanvasService();

        return [
            'test' => \Carbon\Carbon::now()->toDateTimeString(),
        ];
    }
}; ?>

<div>
    {{ $test }}
</div>
