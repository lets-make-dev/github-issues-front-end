<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-white dark:bg-[#0d1117]">
            @livewire('navigation-menu')

            <!-- Page Heading -->

            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

             <!-- Flash Messages -->
             <div class="fixed top-0 right-0 mt-4 mr-4 w-80">
                <div id="successMessage" class="bg-green-500 text-white p-4 rounded shadow animate__animated animate__fadeIn"  style="display: none;">

                </div>
                {{-- <div id="errorMessage" class="bg-red-500 text-white p-4 rounded shadow animate__animated animate__fadeIn">
                </div> --}}
            </div>


            <!-- Page Content -->
            <main class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">

                <h1 class="text-3xl pb-4">@yield('projectName')</h1>

                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
