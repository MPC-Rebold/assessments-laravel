<?php

use Livewire\Volt\Component;
use App\Services\CanvasService;
use App\Models\Course;
use App\Services\SeedReaderService;

new class extends Component {

    public function placeholder(): string
    {
        return <<<'HTML'
        <div>loading</div>
        HTML;

    }


    public function with(): array
    {
        $canvasApi = new CanvasService();
        $filesCourses = SeedReaderService::getCourses();
        $canvasCourses = $canvasApi->getCourses()->json();
        $databaseCourses = Course::all()->pluck('title')->toArray();



        return [
            'databaseCourses' => $databaseCourses,
            'canvasCourses' => $canvasCourses,
            'filesCourses' => $filesCourses,
        ];
    }

}; ?>

<div>

</div>
