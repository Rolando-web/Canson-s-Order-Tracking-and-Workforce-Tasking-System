@extends('partials.app', ['title' => 'Assignments - Canson', 'activePage' => 'assignments'])

@push('styles')
    @vite('resources/css/pages/assignments.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/assignments.js')
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
<div class="assignments-page">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Worker List --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900">Workforce</h3>
            <p class="text-sm text-gray-500 mb-4">Select a worker to manage assignments</p>

            <div class="space-y-1" id="workerList">
                @php
                    $workers = [
                        ['name' => 'Juan Dela Cruz', 'initial' => 'J', 'color' => 'bg-emerald-500', 'status' => 'AVAILABLE', 'statusColor' => 'bg-green-100 text-green-700', 'dept' => 'General', 'active' => 0],
                        ['name' => 'Maria Santos', 'initial' => 'M', 'color' => 'bg-amber-500', 'status' => 'BUSY', 'statusColor' => 'bg-amber-100 text-amber-700', 'dept' => 'Assembly', 'active' => 2],
                        ['name' => 'Pedro Reyes', 'initial' => 'P', 'color' => 'bg-green-500', 'status' => 'AVAILABLE', 'statusColor' => 'bg-green-100 text-green-700', 'dept' => 'Packaging', 'active' => 0],
                        ['name' => 'Ana Lim', 'initial' => 'A', 'color' => 'bg-amber-500', 'status' => 'BUSY', 'statusColor' => 'bg-amber-100 text-amber-700', 'dept' => 'QC', 'active' => 1],
                        ['name' => 'Luis Torres', 'initial' => 'L', 'color' => 'bg-gray-400', 'status' => 'OFFLINE', 'statusColor' => 'bg-gray-100 text-gray-500', 'dept' => 'Logistics', 'active' => 0],
                    ];
                @endphp

                @foreach($workers as $worker)
                <div class="worker-item flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full {{ $worker['color'] }} flex items-center justify-center text-white font-bold text-sm">
                            {{ $worker['initial'] }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $worker['name'] }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $worker['statusColor'] }}">{{ $worker['status'] }}</span>
                                <span class="text-xs text-gray-400">• {{ $worker['dept'] }}</span>
                            </div>
                        </div>
                    </div>
                    @if($worker['active'] > 0)
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-700">{{ $worker['active'] }}</p>
                        <p class="text-xs text-gray-400">Active</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Worker Details --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex flex-col items-center justify-center h-full min-h-[400px] text-center">
                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
                <h3 class="text-lg font-bold text-gray-900">No Worker Selected</h3>
                <p class="text-sm text-gray-500 mt-1">Select a worker from the list to view their assignments</p>
            </div>
        </div>
    </div>
</div>
@endsection
