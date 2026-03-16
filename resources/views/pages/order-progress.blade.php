@extends('partials.app', ['title' => 'Order Progress - Canson', 'activePage' => 'progress'])

@section('content')
<div class="p-1 space-y-6">

    {{-- Page Header + Summary Stats --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Order Progress</h2>
            <p class="text-sm text-gray-500 mt-0.5">Phase-by-phase production &amp; delivery tracking</p>
        </div>
        @php
            $totalOrders  = $orders->count();
            $totalPhases  = $orders->sum(fn($o) => count($o['phases']));
            $urgentOrders = $orders->filter(fn($o) => $o['priority'] === 'Urgent')->count();
            $avgPct       = $totalOrders ? round($orders->avg(fn($o) => $o['overall_pct'])) : 0;
        @endphp
        <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 font-medium uppercase leading-none">Active Orders</p>
                    <p class="text-sm font-bold text-gray-900 leading-tight">{{ $totalOrders }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 font-medium uppercase leading-none">Total Phases</p>
                    <p class="text-sm font-bold text-gray-900 leading-tight">{{ $totalPhases }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 font-medium uppercase leading-none">Avg. Completion</p>
                    <p class="text-sm font-bold text-emerald-600 leading-tight">{{ $avgPct }}%</p>
                </div>
            </div>
            @if($urgentOrders > 0)
            <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-xl px-3 py-2 shadow-sm">
                <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] text-red-500 font-medium uppercase leading-none">Urgent</p>
                    <p class="text-sm font-bold text-red-600 leading-tight">{{ $urgentOrders }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Empty State --}}
    @if($orders->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-2xl border border-dashed border-gray-300">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                </svg>
            </div>
            <p class="text-base font-semibold text-gray-400">No Active Orders</p>
            <p class="text-sm text-gray-400 mt-1">Orders with phased delivery will appear here.</p>
        </div>
    @else
    <div class="space-y-6">
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
        $overallPct  = $order['overall_pct'];
        $phasesCount = count($order['phases']);
    @endphp

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- Order Header --}}
        <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                {{-- Left: Order info --}}
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-base font-bold text-gray-900">{{ $order['id'] }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $statusColor }}">{{ $order['status'] }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $priorityColor }}">{{ $order['priority'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                            <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                            <p class="text-xs text-gray-500 truncate">{{ $order['customer'] }}</p>
                            <span class="text-gray-300 text-xs">&middot;</span>
                            <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                            <p class="text-xs text-gray-400 truncate">{{ $order['address'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Phase stepper + overall progress --}}
                <div class="flex flex-col items-end gap-2 shrink-0">
                    {{-- Phase step indicators --}}
                    <div class="flex items-center gap-1">
                        @foreach($order['phases'] as $ph)
                        @php
                            $dotColor = match($ph['status']) {
                                'Completed'   => 'bg-emerald-500',
                                'Delivered'   => 'bg-teal-500',
                                'In Progress' => 'bg-blue-500',
                                default       => 'bg-gray-200',
                            };
                        @endphp
                        <div class="flex items-center gap-1">
                            <div class="relative group">
                                <div class="w-6 h-6 rounded-full {{ $dotColor }} flex items-center justify-center text-white text-[9px] font-bold shadow-sm cursor-default">
                                    {{ $ph['phase_number'] }}
                                </div>
                                <div class="absolute top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-10">
                                    Phase {{ $ph['phase_number'] }}: {{ $ph['status'] }}
                                </div>
                            </div>
                            @if(!$loop->last)
                            <div class="w-4 h-0.5 {{ $ph['pct'] >= 100 ? 'bg-emerald-400' : 'bg-gray-200' }}"></div>
                            @endif
                        </div>
                        @endforeach
                        <span class="ml-2 text-[10px] text-gray-400">{{ $phasesCount }} phase{{ $phasesCount !== 1 ? 's' : '' }}</span>
                    </div>
                    {{-- Overall progress --}}
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Overall</span>
                        <div class="w-28 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                                {{ $overallPct >= 100 ? 'bg-emerald-500' : ($overallPct > 0 ? 'bg-blue-500' : 'bg-gray-300') }}"
                                style="width: {{ $overallPct }}%"></div>
                        </div>
                        <span class="text-sm font-bold {{ $overallPct >= 100 ? 'text-emerald-600' : 'text-gray-700' }}">{{ $overallPct }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Phases Grid --}}
        <div class="p-4 overflow-x-auto">
            <div class="flex gap-3 min-w-max sm:min-w-0 sm:grid sm:grid-cols-{{ min($phasesCount, 4) }}">
            @foreach($order['phases'] as $phase)
            @php
                $phaseColors = match($phase['status']) {
                    'Completed'   => ['header_bg' => 'bg-emerald-700', 'border' => 'border-emerald-200', 'badge' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-500'],
                    'Delivered'   => ['header_bg' => 'bg-teal-700',    'border' => 'border-teal-200',    'badge' => 'bg-teal-100 text-teal-700',    'dot' => 'bg-teal-500'],
                    'In Progress' => ['header_bg' => 'bg-blue-700',    'border' => 'border-blue-200',    'badge' => 'bg-blue-100 text-blue-700',    'dot' => 'bg-blue-500'],
                    default       => ['header_bg' => 'bg-indigo-700',   'border' => 'border-gray-200',    'badge' => 'bg-gray-100 text-gray-500',    'dot' => 'bg-gray-300'],
                };
                $phasePct  = $phase['pct'];
                $hasDamage = $phase['damage_qty'] > 0;
            @endphp
            <div class="w-60 sm:w-auto flex-shrink-0 sm:flex-shrink rounded-xl border {{ $phaseColors['border'] }} overflow-hidden flex flex-col">

                {{-- Phase Header --}}
                <div class="{{ $phaseColors['header_bg'] }} px-3 py-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="text-white font-bold text-sm">Phase {{ $phase['phase_number'] }}</span>
                            @if($hasDamage)
                            <svg class="w-3.5 h-3.5 text-yellow-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                            </svg>
                            @endif
                        </div>
                        <span class="text-white/80 text-[10px] font-medium">{{ $phase['delivery_date'] }}</span>
                    </div>
                    {{-- Phase Progress Bar --}}
                    <div class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-white/70 text-[10px]">{{ $phase['done_qty'] }} / {{ $phase['total_qty'] }} done</span>
                            <span class="text-white font-bold text-xs">{{ $phasePct }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-white/20 rounded-full overflow-hidden">
                            <div class="h-full bg-white rounded-full transition-all duration-500" style="width: {{ $phasePct }}%"></div>
                        </div>
                    </div>
                    @if($hasDamage)
                    <div class="mt-1.5">
                        <span class="text-[9px] bg-yellow-400/30 text-yellow-200 border border-yellow-400/40 rounded px-1.5 py-0.5 font-semibold">
                            +{{ $phase['damage_qty'] }} damage carried
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Status Badge --}}
                <div class="px-3 py-1.5 border-b {{ $phaseColors['border'] }} bg-white">
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold {{ $phaseColors['badge'] }} rounded-full px-2 py-0.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $phaseColors['dot'] }}"></span>
                        {{ $phase['status'] }}
                    </span>
                </div>

                {{-- Phase Items --}}
                <div class="flex-1 divide-y divide-gray-100">
                    @forelse($phase['items'] as $item)
                    @php
                        $itemPct = $item['pct'];
                        $isDone  = $itemPct >= 100;
                    @endphp
                    <div class="px-3 py-2.5 bg-white hover:bg-gray-50 transition-colors" id="phase-item-{{ $item['id'] }}">
                        <div class="flex items-start justify-between gap-1.5 mb-1.5">
                            <p class="text-xs font-semibold text-gray-800 leading-snug">{{ $item['name'] }}</p>
                            @if($isDone)
                            <div class="w-4 h-4 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </div>
                            @endif
                        </div>
                        @if($item['damage_carry'] > 0)
                        <div class="mb-1.5">
                            <span class="text-[10px] bg-amber-50 text-amber-600 border border-amber-100 rounded px-1.5 py-0.5 font-medium">
                                {{ $item['base_qty'] }} base + {{ $item['damage_carry'] }} dmg = {{ $item['required_qty'] }} req
                            </span>
                        </div>
                        @else
                        <p class="text-[10px] text-gray-400 mb-1.5">Required: <span class="font-semibold text-gray-600">{{ $item['required_qty'] }} pcs</span></p>
                        @endif
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden mb-1.5">
                            <div class="h-full rounded-full transition-all duration-300
                                {{ $isDone ? 'bg-emerald-500' : ($itemPct > 0 ? 'bg-blue-400' : 'bg-gray-300') }}"
                                id="item-bar-{{ $item['id'] }}"
                                style="width: {{ $itemPct }}%"></div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-semibold {{ $isDone ? 'text-emerald-600' : 'text-blue-600' }}">
                                <span class="completed-count-{{ $item['id'] }}">{{ $item['completed_qty'] }}</span> / {{ $item['required_qty'] }} pcs
                            </span>
                            <span class="text-[10px] font-bold {{ $isDone ? 'text-emerald-600' : 'text-gray-500' }}">
                                <span id="item-pct-{{ $item['id'] }}">{{ $itemPct }}</span>%
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="px-3 py-5 text-center">
                        <p class="text-xs text-gray-400">No items in this phase.</p>
                    </div>
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
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Update Progress</h3>
                </div>
                <button onclick="closeUpdateProgress()" class="text-gray-400 hover:text-gray-600 w-7 h-7 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xs text-gray-500 mb-0.5">Item</p>
                    <p class="text-sm font-semibold text-gray-900" id="progressItemName"></p>
                </div>
                <div class="flex gap-3">
                    <div class="flex-1 bg-blue-50 rounded-xl p-3 text-center">
                        <p class="text-[10px] text-blue-500 font-medium uppercase mb-0.5">Completed</p>
                        <p class="text-lg font-bold text-blue-700" id="progressItemCurrent"></p>
                    </div>
                    <div class="flex items-center text-gray-300 text-xl font-light">/</div>
                    <div class="flex-1 bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-[10px] text-gray-500 font-medium uppercase mb-0.5">Required</p>
                        <p class="text-lg font-bold text-gray-700" id="progressItemRequired"></p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Add Completed Qty</label>
                    <input type="number" id="progressAddQty" min="1" value="1"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    <p class="text-[10px] text-gray-400 mt-1.5">Enter how many more pieces were completed.</p>
                </div>
            </div>
            <div class="px-6 pb-5 flex gap-3">
                <button onclick="closeUpdateProgress()" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitProgress()" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
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
