<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Canson' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-100">

    {{-- Sidebar --}}
    <x-side-bar :active="$activePage ?? 'dashboard'" />

    {{-- Main Content --}}
    <div id="mainContent" class="main-content min-h-screen transition-all duration-300">
        <nav class="bg-white shadow px-4 py-3 lg:px-6 lg:py-4 flex items-center gap-4">
            {{-- Mobile Hamburger --}}
            <button id="sidebarMobileToggle"
                    class="lg:hidden flex-shrink-0 w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200
                           flex items-center justify-center transition-colors"
                    aria-label="Open sidebar">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <div class="flex-1">@yield('nav')</div>
        </nav>

        <main class=" sm:px-[120px] p-6">
            @yield('content')
        </main>

        <footer class="px-[120px] py-4 text-sm text-gray-500">
            @yield('footer')
        </footer>
    </div>

    @stack('scripts')
</body>
</html>