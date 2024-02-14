<?php

use Livewire\Volt\Component;
use App\Services\Canvas;

new class extends Component {
    public function with()
    {
        $canvasApi = new Canvas();

        return [
            'canvas' => $canvasApi->getCourses(),
        ];
    }


}; ?>

<div>
    @dd($canvas)
</div>
