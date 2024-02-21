<div class="bg-white shadow sm:rounded-lg">
    <div class="flex h-full w-full bg-white p-4 text-lg font-bold shadow sm:flex-row sm:rounded-lg sm:p-6">
        <h2 class="min-w-16 basis-1/12">
            Status
        </h2>
        <h2 class="min-w-24 basis-2/12">
            Course
        </h2>
        <h2 class="grow">
            Canvas
        </h2>
        <h2 class="basis-1/12">
            Edit
        </h2>
    </div>

    <div class="space-y-4 p-4 sm:p-6">
        @foreach ($masterCourses as $masterCourse)
            <livewire:admin.course-status :masterCourse="$masterCourse" :key="$masterCourse->id" />
            <hr>
        @endforeach
    </div>
</div>
