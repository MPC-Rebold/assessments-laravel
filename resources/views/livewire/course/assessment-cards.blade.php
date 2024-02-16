<?php

use Livewire\Volt\Component;
use App\Models\Assessment;

new class extends Component {
    public array $assessments;
}; ?>

<div>

    <div class="p-6 text-gray-900">
        @foreach($assessments as $assessment)
            <a href="{{route('assessment', [$assessment['course_id'], $assessment['id']])}}">
                {{$assessment['title']}}
            </a>
        @endforeach
    </div>
</div>
