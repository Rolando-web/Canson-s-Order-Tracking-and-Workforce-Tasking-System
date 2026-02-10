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
    <div class="flex items-start justify-between mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-7 h-7 text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Production Schedule</h2>
                <p class="text-gray-500 mt-0.5">Track deadlines, production runs, and facility notes</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            {{-- View Toggle --}}
            <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                <button class="schedule-view-btn px-4 py-2 text-sm font-medium bg-emerald-600 text-white" data-view="month">Month</button>
                <button class="schedule-view-btn px-4 py-2 text-sm font-medium bg-white text-gray-600 hover:bg-gray-50" data-view="week">Week</button>
            </div>
            {{-- Month Navigation --}}
            <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2">
                <button id="prevMonth" class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></button>
                <span id="currentMonth" class="text-sm font-semibold text-gray-700 min-w-[120px] text-center">{{ now()->format('F Y') }}</span>
                <button id="nextMonth" class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></button>
            </div>
            <button class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Today</button>
        </div>
    </div>

    {{-- Calendar --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Day Headers --}}
        <div class="grid grid-cols-7 border-b border-gray-200">
            @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
            <div class="px-3 py-3 text-xs font-semibold text-gray-500 text-center tracking-wider">{{ $day }}</div>
            @endforeach
        </div>

        {{-- Calendar Grid --}}
        @php
            $today = now();
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth = $today->copy()->endOfMonth();
            $startDay = $startOfMonth->dayOfWeek;
            $daysInMonth = $endOfMonth->day;

            $events = [
                10 => ['title' => 'Maintenance', 'desc' => 'Machine A Maintenance', 'color' => 'bg-yellow-100 text-yellow-800'],
            ];
        @endphp

        <div class="grid grid-cols-7" id="calendarGrid">
            {{-- Empty cells before first day --}}
            @for($i = 0; $i < $startDay; $i++)
                <div class="min-h-[120px] border-b border-r border-gray-100 p-2"></div>
            @endfor

            {{-- Day cells --}}
            @for($d = 1; $d <= $daysInMonth; $d++)
            <div class="min-h-[120px] border-b border-r border-gray-100 p-2">
                <span class="inline-flex items-center justify-center w-7 h-7 text-sm rounded-full {{ $d === $today->day ? 'bg-emerald-600 text-white font-bold' : 'text-gray-700' }}">
                    {{ $d }}
                </span>
                @if(isset($events[$d]))
                <div class="mt-1 p-1.5 rounded text-xs {{ $events[$d]['color'] }}">
                    <p class="font-semibold">{{ $events[$d]['title'] }}</p>
                    <p>{{ $events[$d]['desc'] }}</p>
                </div>
                @endif
            </div>
            @endfor

            {{-- Empty cells after last day --}}
            @php $remaining = 7 - (($startDay + $daysInMonth) % 7); @endphp
            @if($remaining < 7)
                @for($i = 0; $i < $remaining; $i++)
                    <div class="min-h-[120px] border-b border-r border-gray-100 p-2"></div>
                @endfor
            @endif
        </div>
    </div>
</div>
@endsection
