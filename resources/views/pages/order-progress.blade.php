@extends('partials.app', ['title' => 'Order Progress - Canson', 'activePage' => 'progress'])

@section('nav')
    <div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">Order Progress</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('l, F d, Y') }}</span>
            <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">
                {{ auth()->user()->initial ?? strtoupper(substr(auth()->user()->name,0,1)) }}
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="p-1">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Order Progress</h2>
            <p class="text-gray-500 mt-1">Phase-by-phase production & delivery tracking — visible to all team members</p>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl border border-gray-200">
            <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
            </svg>
            <p class="text-lg font-semibold text-gray-400">No Phased Orders Yet</p>
            <p class="text-sm text-gray-400 mt-1">Orders with phased delivery will appear here.</p>
        </div>
    @else
        <div class="space-y-8">
        @foreach($orders as $order)
        @php
            $statusColor = match($order['status']) {
                'Completed'          => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'In-Progress'        => 'bg-blue-50 text-blue-700 border-blue-200',
                'Ready for Delivery' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'Delivered'          => 'bg-teal-50 text-teal-700 border-teal-200',
                default              => 'bg-gray-50 text-gray-500 border-gray-200',
            };
            $priorityColor = match($order['priority']) {
                'Urgent' => 'bg-red-50 text-red-600 border-red-200',
                'High'   => 'bg-amber-50 text-amber-600 border-amber-200',
                default  => 'bg-gray-50 text-gray-500 border-gray-200',
            };
            $overallPct = $order['overall_pct'];
        @endphp
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            {{-- Order header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-bold text-gray-900">{{ $order['id'] }}</h3>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium border {{ $statusColor }}">{{ $order['status'] }}</span>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium border {{ $priorityColor }}">{{ $order['priority'] }}</span>
                            <span class="text-xs text-gray-400">{{ count($order['phases']) }} phase{{ count($order['phases']) !== 1 ? 's' : '' }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $order['customer'] }} &bull; {{ $order['address'] }}</p>
                    </div>
                </div>
                {{-- Overall progress --}}
                <div class="shrink-0 text-right hidden sm:block">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Overall Progress</p>
                    <div class="flex items-center gap-2">
                        <div class="w-32 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                                {{ $overallPct >= 100 ? 'bg-emerald-500' : ($overallPct > 0 ? 'bg-blue-500' : 'bg-gray-300') }}"
                                style="width: {{ $overallPct }}%"></div>
                        </div>
                        <span class="text-sm font-bold {{ $overallPct >= 100 ? 'text-emerald-600' : 'text-gray-700' }}">{{ $overallPct }}%</span>
                    </div>
                </div>
            </div>

            {{-- Phases columns --}}
            <div class="p-5 overflow-x-auto">
                <div class="flex gap-4 min-w-max sm:min-w-0 sm:grid sm:grid-cols-{{ min(count($order['phases']), 4) }}">
                @foreach($order['phases'] as $phase)
                @php
                    $phaseStatusColor = match($phase['status']) {
                        'Completed' => ['border' => 'border-emerald-300', 'bg' => 'bg-emerald-600', 'badge' => 'bg-emerald-100 text-emerald-700', 'bar' => 'bg-emerald-500'],
                        'Delivered' => ['border' => 'border-teal-300',    'bg' => 'bg-teal-600',    'badge' => 'bg-teal-100 text-teal-700',    'bar' => 'bg-teal-500'],
                        'In Progress'  => ['border' => 'border-blue-300', 'bg' => 'bg-blue-600',    'badge' => 'bg-blue-100 text-blue-700',    'bar' => 'bg-blue-500'],
                        default        => ['border' => 'border-gray-200', 'bg' => 'bg-indigo-600',    'badge' => 'bg-gray-100 text-gray-500',    'bar' => 'bg-blue-600'],
                    };
                    $phasePct   = $phase['pct'];
                    $hasDamage  = $phase['damage_qty'] > 0;
                @endphp
                <div class="w-64 sm:w-auto flex-shrink-0 sm:flex-shrink border rounded-xl overflow-hidden {{ $phaseStatusColor['border'] }}">
                    {{-- Phase header --}}
                    <div class="{{ $phaseStatusColor['bg'] }} px-3 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-white font-bold text-sm">Phase {{ $phase['phase_number'] }}</span>
                            @if($hasDamage)
                            <svg class="w-4 h-4 text-yellow-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                            </svg>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-white/80 text-[10px]">Delivery</p>
                            <p class="text-white text-xs font-semibold">{{ $phase['delivery_date'] }}</p>
                        </div>
                    </div>

                    {{-- Phase progress bar --}}
                    <div class="px-3 pt-2.5 pb-1 bg-gray-50 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase">Progress</span>
                            <span class="text-xs font-bold {{ $phasePct >= 100 ? 'text-emerald-600' : 'text-gray-700' }}">{{ $phasePct }}%</span>
                        </div>
                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $phaseStatusColor['bar'] }}"
                                style="width: {{ $phasePct }}%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-[10px] text-gray-400">{{ $phase['done_qty'] }} / {{ $phase['total_qty'] }} items done</span>
                            @if($hasDamage)
                            <span class="text-[10px] font-semibold text-amber-600">+{{ $phase['damage_qty'] }} damage carried</span>
                            @endif
                        </div>
                    </div>

                    {{-- Phase items --}}
                    <div class="divide-y divide-gray-100">
                        @forelse($phase['items'] as $item)
                        @php
                            $itemPct = $item['pct'];
                            $isDone  = $itemPct >= 100;
                        @endphp
                        <div class="px-3 py-2.5 bg-white" id="phase-item-{{ $item['id'] }}">
                            <div class="flex items-start justify-between gap-1 mb-1.5">
                                <p class="text-xs font-semibold text-gray-800 leading-tight">{{ $item['name'] }}</p>
                                @if($isDone)
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                @endif
                            </div>
                            @if($item['damage_carry'] > 0)
                            <p class="text-[10px] text-amber-600 font-medium mb-1">
                                {{ $item['base_qty'] }} base + {{ $item['damage_carry'] }} damage = {{ $item['required_qty'] }} required
                            </p>
                            @else
                            <p class="text-[10px] text-gray-400 mb-1">Required: {{ $item['required_qty'] }} pcs</p>
                            @endif
                            <p class="text-[10px] text-blue-600 font-medium mb-1.5">
                                Completed so far: <span class="completed-count-{{ $item['id'] }}">{{ $item['completed_qty'] }}</span> / {{ $item['required_qty'] }} pcs
                            </p>
                            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden mb-2">
                                <div class="h-full rounded-full transition-all duration-300
                                    {{ $isDone ? 'bg-emerald-500' : 'bg-blue-400' }}"
                                    id="item-bar-{{ $item['id'] }}"
                                    style="width: {{ $itemPct }}%"></div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-gray-400">
                                    {{ $item['completed_qty'] }} / {{ $item['required_qty'] }} items &bull;
                                    <span id="item-pct-{{ $item['id'] }}">{{ $itemPct }}</span>% done
                                </span>
                                @if(!$isDone)
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="px-3 py-4 text-center text-xs text-gray-400">No items in this phase.</div>
                        @endforelse
                    </div>
                </div>
                @endforeach
                </div>
            </div>
        </div>
        @endforeach
        </div>
    @endif
</div>

{{-- Update Progress Modal --}}
<div id="updateProgressModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeUpdateProgress()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" onclick="event.stopPropagation()">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900">Update Item Progress</h3>
                <button onclick="closeUpdateProgress()" class="text-gray-400 hover:text-gray-600 w-7 h-7 rounded-lg hover:bg-gray-100 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-5">
                <p class="text-sm text-gray-600 mb-1">Item: <span id="progressItemName" class="font-semibold text-gray-900"></span></p>
                <p class="text-sm text-gray-500 mb-4">Current: <span id="progressItemCurrent" class="font-semibold"></span> / <span id="progressItemRequired" class="font-semibold"></span></p>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Add Completed Qty</label>
                <input type="number" id="progressAddQty" min="1" value="1"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-gray-400 mt-1.5">Enter how many more pieces you completed.</p>
            </div>
            <div class="px-6 pb-5 flex gap-3">
                <button onclick="closeUpdateProgress()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button onclick="submitProgress()" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Save Progress
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/pages/order-progress.js')
@endpush