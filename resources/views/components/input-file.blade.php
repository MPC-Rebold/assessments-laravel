<div class="flex w-full items-center justify-center">
    <label for="dropzone-file"
        class="dark:hover:bg-bray-800 flex h-48 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:border-gray-500 dark:hover:bg-gray-600">
        <div class="flex flex-col items-center justify-center pb-6 pt-5">
            <svg class="mb-4 h-8 w-8 text-gray-500 dark:text-gray-400" aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
            </svg>
            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span>
                or drag and drop</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">TXT (max. 1MB
                each)</p>
        </div>
        <input id="dropzone-file" type="file" class="hidden" multiple />
    </label>
</div>
