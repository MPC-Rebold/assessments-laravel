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
        'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-medium focus:outline-none transition duration-150 ease-in-out';

    $classes = $active ?? false ? $base . ' ' . $colorsActive : $base . ' ' . $colorsInactive;
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
