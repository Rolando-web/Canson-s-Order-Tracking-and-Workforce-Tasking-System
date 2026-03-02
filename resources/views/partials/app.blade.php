<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Canson School &amp; Office Supplies – Internal Management System">
    <meta name="theme-color" content="#10b981">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Canson' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    {{-- Font: Instrument Sans (preconnect first, then stylesheet) --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap"></noscript>
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

            {{-- Notification Bell for All Users --}}
            @auth
                @php
                    $unreadNotifCount = \Illuminate\Support\Facades\Cache::remember(
                        'notif_unread_' . auth()->id(),
                        30,
                        fn() => \App\Models\Notification::where('user_id', auth()->id())
                            ->where('is_read', false)
                            ->count()
                    );
                @endphp
                <a href="{{ route('notifications') }}" class="relative w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors" title="Notifications" aria-label="View notifications">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                    @if($unreadNotifCount > 0)
                        <span id="navBellBadge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                    @else
                        <span id="navBellBadge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center hidden"></span>
                    @endif
                </a>
            @endauth
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