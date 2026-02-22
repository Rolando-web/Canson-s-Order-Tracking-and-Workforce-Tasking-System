@extends('partials.app', ['title' => 'Dispatch - Canson', 'activePage' => 'dispatch'])

@push('styles')
    @vite('resources/css/pages/dispatch.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/dispatch.js')
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
<div class="dispatch-page">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Dispatch & Delivery</h2>
        <p class="text-gray-500 mt-1">Manage shipments and delivery receipts</p>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        {{-- Ready to Ship --}}
        <div class="bg-white rounded-xl flex justify-center border-2 border-emerald-500 p-5 cursor-pointer">
           <div class="flex justify-center flex-col">
             <div class="w-8 h-8 md:w-10 rounded-xl flex-none bg-emerald-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                </svg>
            </div>
           </div>
        <div class="grow flex flex-col justify-center">
            <p class="text-sm font-medium text-emerald-600 text-end">Ready to Ship</p>
            <p class="text-3xl font-bold text-gray-900 mt-1 text-end">{{ $readyToShip ?? 0 }}</p>
        </div>
        </div>

        {{-- In Transit --}}
        <div class="bg-white rounded-xl flex justify-center border border-gray-200 p-5 cursor-pointer hover:border-gray-300 transition-colors">
            <div class="flex justify-center flex-col">
            <div class="w-8 h-8 md:w-10 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25"/>
                </svg>
            </div>
            </div>
           <div class="grow flex flex-col justify-center">
             <p class="text-sm font-medium text-gray-500 text-end">In Transit</p>
            <p class="text-3xl font-bold text-gray-900 mt-1 text-end">{{ $inTransit ?? 0 }}</p>
           </div>
        </div>

        {{-- Delivered --}}
        <div class="bg-white rounded-xl flex justify-center border border-gray-200 p-5 cursor-pointer hover:border-gray-300 transition-colors">
        <div class="flex justify-center flex-col">  
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div> 
            <div class="grow flex flex-col justify-center">
            <p class="text-sm font-medium text-gray-500 text-end">Delivered</p>
            <p class="text-3xl font-bold text-gray-900 mt-1 text-end">{{ $delivered ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input id="dispatchSearch" type="text" placeholder="Search shipments..." oninput="filterDispatch()" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <select id="dispatchStatusFilter" onchange="filterDispatch()" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_transit">In Transit</option>
                <option value="delivered">Delivered</option>
                <option value="failed">Failed</option>
            </select>
            <select id="dispatchDateFilter" onchange="filterDispatch()" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">All Dates</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
    </div>

    {{-- Shipment Cards --}}
    <div class="space-y-4">
        @foreach($dispatches as $dispatch)
        <div class="dispatch-card bg-white rounded-xl border border-gray-200 p-6" data-status="{{ $dispatch['status'] }}" data-date="{{ $dispatch['date'] }}" data-search="{{ strtolower($dispatch['customer'] . ' ' . $dispatch['order_id'] . ' ' . $dispatch['driver']) }}">
            <div class="flex flex-col gap-4">
                {{-- Header with Status and Actions --}}
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">{{ $dispatch['order_id'] }}</span>
                        @php
                            $statusConfig = match($dispatch['status']) {
                                'pending' => ['label' => 'Pending', 'color' => 'bg-gray-50 text-gray-600 border-gray-200'],
                                'in_transit' => ['label' => 'In Transit', 'color' => 'bg-blue-50 text-blue-600 border-blue-200'],
                                'delivered' => ['label' => 'Delivered', 'color' => 'bg-green-50 text-green-600 border-green-200'],
                                'failed' => ['label' => 'Failed', 'color' => 'bg-red-50 text-red-600 border-red-200'],
                                default => ['label' => 'Unknown', 'color' => 'bg-gray-50 text-gray-500 border-gray-200'],
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusConfig['color'] }}">{{ $statusConfig['label'] }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247"/></svg>
                            Print DR
                        </button>
                        @if($dispatch['status'] === 'pending')
                        <button class="flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-medium transition-colors">
                            Mark Shipped
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </button>
                        @elseif($dispatch['status'] === 'in_transit')
                        <button class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium transition-colors">
                            Mark Delivered
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Customer and Order Info --}}
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $dispatch['customer'] }}</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $dispatch['items'] }}</p>
                    <p class="flex items-center gap-1 text-sm text-gray-400 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        {{ $dispatch['address'] }}
                    </p>
                </div>

                {{-- Driver and Vehicle Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Assigned Driver</label>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-bold">
                                {{ $dispatch['driver_initial'] }}
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $dispatch['driver'] }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Vehicle</label>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25"/></svg>
                            <span class="text-sm text-gray-900">{{ $dispatch['vehicle'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Timing Info --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Scheduled Date</label>
                        <div class="flex items-center gap-1 text-sm text-gray-900">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                            {{ date('M d, Y', strtotime($dispatch['date'])) }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Dispatch Time</label>
                        <div class="text-sm text-gray-900">
                            {{ $dispatch['dispatch_time'] ? date('M d, Y h:i A', strtotime($dispatch['dispatch_time'])) : 'Not dispatched' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Delivery Time</label>
                        <div class="text-sm text-gray-900">
                            {{ $dispatch['delivery_time'] ? date('M d, Y h:i A', strtotime($dispatch['delivery_time'])) : 'Pending' }}
                        </div>
                    </div>
                </div>

                {{-- Assigned By --}}
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Assigned by: <span class="font-medium text-gray-700">{{ $dispatch['assigned_by'] }}</span></p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
