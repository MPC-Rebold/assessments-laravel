<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @hasSection('title')
        <title>{{ config('app.name', 'Rebold') }} - @yield('title')</title>
    @else
        <title>{{ config('app.name', 'Rebold') }}</title>
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <wireui:scripts />
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-400 pb-16">
        <x-application-logo-full class="w-80 select-none fill-current text-gray-500" />
        <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-sm sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
    <div class="absolute bottom-4 w-full text-center text-slate-300">
        <div class="group sm:absolute sm:bottom-0 sm:right-4">
            <a href="https://github.com/MPC-Rebold/assessments-laravel" target="_blank">
                <x-icon-github
                    class="inline-block h-6 w-6 text-slate-300 transition-all ease-in-out group-hover:scale-125 group-hover:text-slate-200" />
            </a>
        </div>
        <div class="text-sm">
            &copy; 2024 Tom Rebold, Angel Vasquez, Andrew Wang
        </div>
    </div>
</body>

</html>
