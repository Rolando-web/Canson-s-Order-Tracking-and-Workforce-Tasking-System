@extends('partials.app', ['title' => 'Schedule - Canson', 'activePage' => 'schedule'])

@push('styles')
    @vite('resources/css/pages/schedule.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/schedule.js')
@endpush

@section('nav')
    <div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">Manager</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('l, F d, Y') }}</span>
            <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">AD</div>
        </div>
    </div>
@endsection

@section('content')
<div class="schedule-page">
    {{-- Header --}}
    <div class="schedule-header">
        <div class="schedule-header-left">
            <svg class="w-7 h-7 text-gray-700 mobile-hide" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Production Schedule</h2>
                <p class="text-gray-500 mt-0.5 mobile-hide">Track deadlines, production runs, and facility notes</p>
            </div>
        </div>
        <div class="schedule-header-right">
            {{-- View Toggle --}}
            <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                <button class="schedule-view-btn px-4 py-2 text-sm font-medium bg-emerald-600 text-white" data-view="month">Month</button>
                <button class="schedule-view-btn px-4 py-2 text-sm font-medium bg-white text-gray-600 hover:bg-gray-50" data-view="week">Week</button>
            </div>
            {{-- Navigation --}}
            <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2">
                <button id="prevPeriod" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <span id="currentPeriod" class="text-sm font-semibold text-gray-700 min-w-[120px] text-center">{{ now()->format('F Y') }}</span>
                <button id="nextPeriod" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            <button id="todayBtn" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">Today</button>
        </div>
    </div>

    {{-- Calendar --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Day Headers --}}
        <div class="grid grid-cols-7 border-b border-gray-200" id="dayHeaders">
            @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
            <div class="px-3 py-3 text-xs font-semibold text-gray-500 text-center tracking-wider">{{ $day }}</div>
            @endforeach
        </div>

        {{-- Calendar Grid - Will be dynamically populated --}}
        <div class="grid grid-cols-7" id="calendarGrid">
            {{-- Dynamically populated by JavaScript --}}
        </div>
    </div>
</div>
@endsection
