<?php

use Livewire\Volt\Component;
use App\Services\CanvasService;

new class extends Component {
    public function with(): array
    {
        $canvasApi = new CanvasService();

        return [
            'canvas' => $canvasApi->getCourses()->json(),
        ];
    }
}; ?>

<div>
    <livewire:dashboard.get-courses/>
    @dd($canvas)
</div>
