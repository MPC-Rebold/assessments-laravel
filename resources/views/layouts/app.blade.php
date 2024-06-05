<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @hasSection('title')
        <title>{{ config('app.name', 'Assessem') }} - @yield('title')</title>
    @else
        <title>{{ config('app.name', 'Assessem') }}</title>
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @wireUiScripts
</head>

<body class="font-sans antialiased">

    <x-notifications position="bottom-right" />

    <div class="min-h-screen bg-slate-100">
        <livewire:layout.navigation />

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
            @if (isset($content))
                <div class="py-10">
                    <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                        {{ $content }}
                    </div>
                </div>
            @endif
        </main>
    </div>
</body>

</html>
