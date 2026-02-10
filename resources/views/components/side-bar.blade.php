{{-- Mobile Overlay --}}
@props(['active' => 'dashboard'])

<div id="sidebarOverlay"
     class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm hidden lg:hidden"
     aria-hidden="true"></div>

{{-- Sidebar Component --}}
<aside id="sidebar"
       class="sidebar fixed top-0 left-0 z-40 h-screen flex flex-col
              bg-sidebar text-white transition-all duration-300 ease-in-out
              w-64"
       data-collapsed="false">

    {{-- Logo / Brand --}}
    <div class="flex items-center justify-between px-5 py-5 border-b border-white/10">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="sidebar-icon flex-shrink-0 w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center font-bold text-xl">
                C
            </div>
            <span class="sidebar-label text-lg font-semibold whitespace-nowrap">Canson</span>
        </div>

        {{-- Toggle Button --}}
        <button id="sidebarToggle"
                class="sidebar-label flex-shrink-0 w-7 h-7 rounded-full bg-white/10 hover:bg-white/20
                       flex items-center justify-center transition-colors"
                aria-label="Toggle sidebar">
            <svg class="toggle-icon w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    {{-- Collapsed Toggle (visible only when collapsed) --}}
    <button id="sidebarToggleCollapsed"
            class="hidden absolute top-5 right-0 translate-x-1/2 w-7 h-7 rounded-full bg-green-600 hover:bg-green-700
                   flex items-center justify-center transition-colors z-50"
            aria-label="Expand sidebar">
        <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor"
             stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Navigation Links --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4 space-y-1">
        <x-side-bar-link icon="dashboard" label="Dashboard" :active="$active === 'dashboard'" href="/dashboard" />
        <x-side-bar-link icon="schedule"  label="Schedule"  :active="$active === 'schedule'" href="/schedule" />
        <x-side-bar-link icon="orders"    label="Orders"    :active="$active === 'orders'" href="/orders" />
        <x-side-bar-link icon="dispatch"  label="Dispatch"  :active="$active === 'dispatch'" href="/dispatch" />
        <x-side-bar-link icon="assignments" label="Assignments" :active="$active === 'assignments'" href="/assignments" />
        <x-side-bar-link icon="inventory" label="Inventory" :active="$active === 'inventory'" href="/inventory" />
        <x-side-bar-link icon="analytics" label="Analytics" :active="$active === 'analytics'" href="/analytics" />
        <x-side-bar-link icon="employees" label="Employees" :active="$active === 'employees'" href="/employees" />
    </nav>

    {{-- Bottom Section --}}
    <div class="mt-auto border-t border-white/10 px-3 py-4 space-y-1">
        <x-side-bar-link icon="settings" label="Settings" :active="$active === 'settings'" href="/settings" />
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" data-tooltip="Logout"
                    class="sidebar-link group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-red-400 hover:bg-white/5 w-full text-left">
                <span class="sidebar-icon flex-shrink-0 w-5 h-5 flex items-center justify-center">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                </span>
                <span class="sidebar-label whitespace-nowrap overflow-hidden">Logout</span>
            </button>
        </form>
    </div>

    {{-- Storage Usage --}}
    <div class="sidebar-label px-5 pb-5">
        <div class="rounded-lg bg-white/5 p-3">
            <p class="text-xs text-gray-400 mb-2">Storage Usage</p>
            <div class="storage-bar-bg w-full h-1.5 rounded-full overflow-hidden">
                <div class="storage-bar-fill h-full rounded-full" style="width: 70%"></div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-400">
                <span>70% Used</span>
                <span>7k / 10k</span>
            </div>
        </div>
    </div>
</aside>
