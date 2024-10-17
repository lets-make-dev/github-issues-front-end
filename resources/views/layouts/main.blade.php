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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        <!-- Add FontAwesome for trendy icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- AlpineJS -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-gray-100 min-h-screen text-gray-900">
        <x-banner />
        <header class="bg-gray-900 text-white p-4 sticky top-0 z-50 border-b-4 border-white">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-3xl font-bold text-white">{{ $repoName ?? 'GitHub Issues Dashboard' }}</h1>
                <nav>
                    <a href="{{ route('project.settings', [ 'project' => 1]) }}" class="text-white hover:text-gray-300 ml-4 transition-colors duration-300"><i class="fas fa-cog mr-2"></i>Project Settings</a>

                </nav>
            </div>
        </header>

        <main class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
