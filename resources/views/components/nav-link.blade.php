@props(['active', 'style' => 'default'])

@php
    switch ($style) {
        case 'danger':
            $colorsActive = 'border-red-400 text-red-500 focus:border-red-700';
            $colorsInactive = 'border-transparent text-red-500 hover:text-red-700 hover:border-red-300 focus:text-red-700 focus:border-red-300';
            break;
        case 'default':
            $colorsActive = 'border-indigo-400 text-gray-900 focus:border-indigo-700';
            $colorsInactive = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300';
            break;
    }

    $base = 'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out';

    $classes = ($active ?? false)
        ? $base . ' ' . $colorsActive
        : $base . ' ' . $colorsInactive;
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
