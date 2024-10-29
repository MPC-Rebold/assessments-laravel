<div class='border border-warning-500 bg-warning-50 p-4 text-warning-800 sm:rounded-lg'>
    <div class="flex items-center border-b-2 border-warning-200 pb-3">
        <x-icon name="exclamation" class="h-6 w-6" />
        <span class="ml-1 text-lg font-semibold">
            Warning
        </span>
    </div>
    <div class="ml-5 mt-2 pl-1">
        {{ $slot }}
    </div>
</div>
