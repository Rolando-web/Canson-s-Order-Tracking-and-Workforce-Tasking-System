@extends('partials.app', ['title' => 'Dashboard - Canson', 'activePage' => 'dashboard'])

@push('styles')
    @vite('resources/css/pages/dashboard.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/dashboard.js')
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
<div class="dashboard-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Overview</h2>
            <p class="text-gray-500 mt-1">Welcome back, here's what's happening today.</p>
        </div>
        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
            Generate Report
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Orders --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Orders</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">5</p>
                    <p class="text-xs text-green-500 mt-1">+12% from last month</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">2</p>
                    <p class="text-xs text-gray-400 mt-1">Requires attention</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-orange-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- In Progress --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">In Progress</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">2</p>
                    <p class="text-xs text-gray-400 mt-1">Active production</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Completed --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">1</p>
                    <p class="text-xs text-gray-400 mt-1">This month</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Weekly Production Output --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Weekly Production Output</h3>
            <div class="chart-container" id="weeklyChart">
                <div class="flex items-end justify-between gap-3 h-64 px-2">
                    @php
                        $days = ['Mon' => 12, 'Tue' => 18, 'Wed' => 14, 'Thu' => 22, 'Fri' => 28, 'Sat' => 10];
                        $max = 28;
                    @endphp
                    @foreach($days as $day => $val)
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div class="w-full flex flex-col items-center justify-end h-52">
                            <div class="w-8 bg-emerald-500 rounded-t-md transition-all duration-500" style="height: {{ ($val/$max)*100 }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $day }}</span>
                    </div>
                    @endforeach
                </div>
                {{-- Y-axis labels --}}
                <div class="absolute left-0 top-0 h-52 flex flex-col justify-between text-xs text-gray-400 py-0">
                </div>
            </div>
        </div>

        {{-- Order Status Distribution --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Order Status Distribution</h3>
            <div class="flex items-center justify-center gap-8">
                {{-- Donut Chart --}}
                <div class="relative w-44 h-44">
                    <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#3b82f6" stroke-width="4"
                                stroke-dasharray="35.2 52.8" stroke-dashoffset="0" stroke-linecap="round"/>
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#10b981" stroke-width="4"
                                stroke-dasharray="35.2 52.8" stroke-dashoffset="-35.2" stroke-linecap="round"/>
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#f59e0b" stroke-width="4"
                                stroke-dasharray="17.6 70.4" stroke-dashoffset="-70.4" stroke-linecap="round"/>
                    </svg>
                </div>
                {{-- Legend --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <div>
                            <p class="text-sm text-gray-500">Pending</p>
                            <p class="font-bold text-gray-900">2 Orders</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                        <div>
                            <p class="text-sm text-gray-500">In Progress</p>
                            <p class="font-bold text-gray-900">2 Orders</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <div>
                            <p class="text-sm text-gray-500">Completed</p>
                            <p class="font-bold text-gray-900">1 Orders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
