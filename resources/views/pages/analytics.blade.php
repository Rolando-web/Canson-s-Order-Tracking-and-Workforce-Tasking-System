@extends('partials.app', ['title' => 'Analytics - Canson', 'activePage' => 'analytics'])

@push('styles')
    @vite('resources/css/pages/analytics.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/analytics.js')
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
<div class="analytics-page">
    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Weekly Production Output --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Weekly Production Output</h3>
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
        </div>

        {{-- Order Status Distribution --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Order Status Distribution</h3>
            <div class="flex items-center justify-center gap-8">
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

    {{-- Worker Efficiency --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Worker Efficiency</h3>
        <div class="space-y-5">
            @php
                $workers = [
                    ['name' => 'Juan Dela Cruz', 'initial' => 'J', 'color' => 'bg-emerald-500', 'load' => 0],
                    ['name' => 'Maria Santos', 'initial' => 'M', 'color' => 'bg-amber-500', 'load' => 40],
                    ['name' => 'Pedro Reyes', 'initial' => 'P', 'color' => 'bg-green-500', 'load' => 0],
                    ['name' => 'Ana Lim', 'initial' => 'A', 'color' => 'bg-purple-500', 'load' => 20],
                    ['name' => 'Luis Torres', 'initial' => 'L', 'color' => 'bg-gray-400', 'load' => 0],
                ];
            @endphp
            @foreach($workers as $worker)
            <div class="flex items-center gap-4">
                <div class="w-9 h-9 rounded-full {{ $worker['color'] }} flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                    {{ $worker['initial'] }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $worker['name'] }}</span>
                        <span class="text-sm text-gray-500">{{ $worker['load'] }}% Load</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $worker['load'] }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
