<?php

use Livewire\Volt\Component;
use App\Services\Canvas;


new class extends Component {
    public function with(): array {
        $canvas = new Canvas();

        return [
            'test' => 'hi'
        ];
    }
}; ?>

<div>
    {{ $test }}
</div>
