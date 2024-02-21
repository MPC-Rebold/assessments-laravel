<div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
    @foreach($masterCourses as $masterCourse)
        <livewire:admin.course-status :masterCourse="$masterCourse" :key="$masterCourse->id"/>
    @endforeach
</div>