<?php

use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'courses' => glob(database_path('seed'). '\*', GLOB_ONLYDIR),
        ];
    }

}; ?>

<div>
    {{ implode($courses) }}
</div>
