{{-- Sidebar Dropdown Sub-Component --}}
@props([
    'icon'   => '',
    'label'  => '',
    'active' => false,
    'open'   => false,
])

@php
    $baseClasses = 'sidebar-link sidebar-dropdown-toggle group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 w-full cursor-pointer';

    if ($active) {
        $stateClasses = 'text-emerald-400 bg-white/5';
    } else {
        $stateClasses = 'text-gray-300 hover:bg-white/5 hover:text-white';
    }
@endphp

<div class="sidebar-dropdown" data-dropdown-open="{{ $open ? 'true' : 'false' }}">
    {{-- Dropdown Toggle --}}
    <button type="button" data-tooltip="{{ $label }}" class="{{ $baseClasses }} {{ $stateClasses }}">
        <span class="sidebar-icon flex-shrink-0 w-5 h-5 flex items-center justify-center">
            @switch($icon)
                @case('inventory')
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                    </svg>
                    @break
                @default
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>
                    </svg>
            @endswitch
        </span>
        <span class="sidebar-label whitespace-nowrap overflow-hidden flex-1 text-left">{{ $label }}</span>
        <svg class="sidebar-label sidebar-dropdown-chevron w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    {{-- Dropdown Items --}}
    <div class="sidebar-dropdown-menu overflow-hidden transition-all duration-200"
         style="{{ $open ? '' : 'max-height: 0;' }}">
        <div class="pl-8 pr-2 py-1 space-y-0.5">
            {{ $slot }}
        </div>
    </div>
</div>
