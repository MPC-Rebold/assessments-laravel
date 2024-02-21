@props(['active', 'style' => 'default'])

@php
    switch ($style) {
        case 'danger':
            $colorsActive = 'border-red-400 text-red-400 focus:border-red-600';
            $colorsInactive = 'border-transparent text-red-400 hover:text-red-600 hover:border-red-300';
            break;
        case 'default':
            $colorsActive = 'border-slate-300 text-gray-900 bg-slate-100';
            $colorsInactive =
                'border-transparent text-slate-200 hover:text-white hover:border-white focus:border-white focus:text-white';
            break;
    }

    $base =
        'inline-flex items-center px-4 pt-1 border-b-4 text-sm font-medium leading-5 focus:outline-none transition-all duration-150 ease-in-out';

    $classes = $active ?? false ? $base . ' ' . $colorsActive : $base . ' ' . $colorsInactive;
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
