@props(['active', 'style' => 'default'])

@php
    switch ($style) {
        case 'danger':
            $colorsActive = 'border-red-400 text-red-500 focus:border-red-700';
            $colorsInactive = 'border-transparent text-red-500 hover:text-red-700 hover:bg-red-50 hover:border-red-300 focus:text-red-700 focus:bg-red-50 focus:border-red-300';
            break;
        case 'default':
            $colorsActive = 'border-indigo-400 text-gray-900 focus:border-indigo-700';
            $colorsInactive = 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300 focus:text-gray-700 focus:bg-gray-50 focus:border-gray-300';
            break;
    }

    $base = 'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-medium focus:outline-none transition duration-150 ease-in-out';

    $classes = ($active ?? false)
        ? $base . ' ' . $colorsActive
        : $base . ' ' . $colorsInactive;
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>