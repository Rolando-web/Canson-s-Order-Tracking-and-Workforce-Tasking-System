@extends('partials.app', ['title' => 'Assignments - Canson', 'activePage' => 'assignments'])

@push('styles')
    @vite('resources/css/pages/assignments.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/assignments.js')
@endpush


@section('content')
<div class="assignments-page">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Worker List --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900">Workforce</h3>
            <p class="text-sm text-gray-500 mb-4">Select a worker to manage assignments</p>

            <div class="space-y-1 flex-col md:flex-row" id="workerList">
                @foreach($workers as $worker)
                <div class="worker-item flex items-center flex-row lg:flex-col xl:flex-row justify-between p-3 rounded-lg hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200 transition-all" onclick="showWorkerAssignments('{{ $worker['name'] }}', '{{ $worker['initial'] }}', '{{ $worker['color'] }}', '{{ $worker['status'] }}', {{ $worker['active'] }})">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full {{ $worker['color'] }} flex items-center justify-center text-white font-bold text-sm">
                            {{ $worker['initial'] }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $worker['name'] }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $worker['statusColor'] }}">{{ $worker['status'] }}</span>
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
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 min-h-[400px]">
            {{-- Empty State --}}
            <div id="emptyState" class="flex flex-col items-center justify-center h-full text-center">
                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
                <h3 class="text-lg font-bold text-gray-900">No Worker Selected</h3>
                <p class="text-sm text-gray-500 mt-1">Select a worker from the list to view their assignments</p>
            </div>

            {{-- Worker Assignments --}}
            <div id="workerAssignments" class="hidden">
                {{-- Worker Header --}}
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div id="workerAvatar" class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center text-white font-bold"></div>
                        <div>
                            <h3 id="workerName" class="text-lg font-bold text-gray-900"></h3>
                            <span id="workerStatus" class="inline-flex px-2 py-0.5 rounded text-xs font-semibold"></span>
                        </div>
                    </div>
                    <button onclick="openAssignmentModal(document.getElementById('workerName').textContent)" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        New Assignment
                    </button>
                </div>

                {{-- Assignments List --}}
                <div class="space-y-4" id="assignmentsList">
                    {{-- Dynamically populated --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Orders Section --}}
    <div class="mt-6 bg-white rounded-xl border border-gray-200 p-3">
        {{-- Header with Toggle --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                    </svg>
                    <span id="ordersSectionTitle">Unassigned Orders</span>
                </h3>
                <p class="text-sm text-gray-500" id="ordersSectionSubtitle">Unassigned orders ready to be assigned to employees</p>
            </div>
            <div class="flex items-center flex-col sm:flex-row gap-3">
                <span id="ordersTabCount" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-700">{{ count($availableOrders) }} orders</span>
                {{-- Toggle --}}
                <div class="flex items-center flex-col sm:flex-row bg-gray-100 rounded-lg p-1">
                    <button id="tabUnassigned" onclick="switchOrdersTab('unassigned')" class="px-3 py-1.5 text-xs font-semibold rounded-md bg-white text-emerald-700 shadow-sm transition-all">
                        Unassigned
                    </button>
                    <button id="tabActive" onclick="switchOrdersTab('active')" class="px-6 py-1.5 text-xs font-semibold rounded-md text-gray-500 hover:text-gray-700 transition-all">
                        Active
                    </button>
                </div>
            </div>
        </div>

        {{-- Unassigned Orders Panel --}}
        <div id="panelUnassigned">
            @if(count($availableOrders) === 0)
            <div class="text-center py-10">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 font-medium">All orders have been assigned!</p>
                <p class="text-gray-400 text-sm mt-1">New unassigned orders will appear here</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" id="availableOrdersGrid">
                @foreach($availableOrders as $order)
                <div class="available-order-card border-2 border-gray-200 rounded-xl p-4 hover:border-emerald-400 hover:shadow-md transition-all group" data-order-id="{{ $order['order_id'] }}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">{{ $order['order_id'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $order['customer'] }}</p>
                            </div>
                        </div>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-600 border border-blue-200">UNASSIGNED</span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-center flex-col sm:flex-row gap-2 text-xs text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                            <span class="truncate" title="{{ $order['items'] }}">{{ Str::limit($order['items'], 40) }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                            Deliver by {{ \Carbon\Carbon::parse($order['delivery_date'])->format('M d, Y') }}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <span class="truncate" title="{{ $order['delivery_address'] }}">{{ Str::limit($order['delivery_address'], 35) }}</span>
                        </div>
                        @if(!empty($order['all_phases_status']))
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($order['all_phases_status'] as $phaseInfo)
                                @php
                                    $badgeClass = match($phaseInfo['status_label']) {
                                        'Pending'   => 'bg-amber-50 text-amber-600 border-amber-200',
                                        'Assigned'  => 'bg-blue-50 text-blue-600 border-blue-200',
                                        'Completed' => 'bg-green-50 text-green-600 border-green-200',
                                        'Delivered' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                        default     => 'bg-gray-100 text-gray-500 border-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-semibold border {{ $badgeClass }}">
                                    P{{ $phaseInfo['number'] }}: {{ $phaseInfo['status_label'] }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <p class="text-sm font-bold text-emerald-600">₱{{ number_format((float)$order['total_amount'], 2) }}</p>
                        <button onclick="quickAssignOrder('{{ $order['order_id'] }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Assign
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Active Orders Panel --}}
        <div id="panelActive" class="hidden">
            @php $activeOrders = $activeOrders ?? []; @endphp
            @if(count($activeOrders) === 0)
            <div class="text-center py-10">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                </svg>
                <p class="text-gray-500 font-medium">No active orders</p>
                <p class="text-gray-400 text-sm mt-1">Orders in progress will appear here</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($activeOrders as $order)
                @php
                    $statusColor = match($order['status']) {
                        'In-Progress' => 'bg-blue-50 text-blue-600 border-blue-200',
                        'Pending'     => 'bg-gray-50 text-gray-500 border-gray-200',
                        default       => 'bg-gray-50 text-gray-500 border-gray-200',
                    };
                    $priorityColor = match($order['priority']) {
                        'urgent' => 'bg-red-50 text-red-600 border-red-200',
                        'high'   => 'bg-orange-50 text-orange-600 border-orange-200',
                        default  => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                    };
                @endphp
                <div class="border-2 flex-col sm:flex-row border-gray-200 rounded-xl p-4 hover:shadow-md transition-all">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">{{ $order['order_id'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $order['customer'] }}</p>
                            </div>
                        </div>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusColor }}">{{ strtoupper($order['status']) }}</span>
                    </div>

                    {{-- Per-phase progress summary --}}
                    @if(count($order['phases_progress'] ?? []) > 0)
                    <div class="space-y-2 mb-3">
                        @foreach($order['phases_progress'] as $phase)
                        @php
                            $totalReq = collect($phase['items'])->sum('required_qty');
                            $totalDone = collect($phase['items'])->sum('completed_qty');
                            $phasePct = $totalReq > 0 ? round(($totalDone / $totalReq) * 100) : 0;
                            $phaseColor = match($phase['status']) {
                                'Completed' => 'text-green-600 bg-green-50 border-green-200',
                                'Delivered' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                                default     => 'text-blue-600 bg-blue-50 border-blue-200',
                            };
                            $barColor = $phasePct >= 100 ? 'bg-emerald-500' : ($phasePct > 0 ? 'bg-blue-400' : 'bg-gray-200');
                        @endphp
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-indigo-600 w-8 flex-shrink-0">P{{ $phase['number'] }}</span>
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="{{ $barColor }} h-full rounded-full" style="width:{{ $phasePct }}%"></div>
                            </div>
                            <span class="text-[10px] font-semibold text-gray-500 w-8 text-right">{{ $phasePct }}%</span>
                            <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-semibold border {{ $phaseColor }}">{{ $phase['status'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="space-y-2 mb-3">
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                            <span class="truncate" title="{{ $order['items'] }}">{{ Str::limit($order['items'], 40) }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="space-y-2 mb-3">
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                            Deliver by {{ \Carbon\Carbon::parse($order['delivery_date'])->format('M d, Y') }}
                        </div>
                        @if(count($order['assigned_to']) > 0)
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            <span class="truncate">{{ implode(', ', $order['assigned_to']) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center flex-col sm:flex-row  justify-between pt-3 border-t border-gray-100">
                        <p class="text-sm font-bold text-emerald-600">₱{{ number_format((float)$order['total_amount'], 2) }}</p>
                        <div class="flex items-center mt-3 gap-2">
                            <button onclick="openOrderProgressModal('{{ $order['order_id'] }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                View Details
                            </button>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $priorityColor }}">{{ strtoupper($order['priority']) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <script>
    const _unassignedCount = {{ count($availableOrders) }};
    const _activeCount = {{ count($activeOrders ?? []) }};
    const _activeOrdersData = @json(collect($activeOrders ?? [])->keyBy('order_id'));
    </script>
</div>

{{-- Order Progress Modal --}}
<div id="orderProgressModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeOrderProgressModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" id="opModalTitle">Order Progress</h3>
                    <p class="text-sm text-gray-500 mt-0.5" id="opModalCustomer"></p>
                </div>
                <button onclick="closeOrderProgressModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            {{-- Body --}}
            <div class="overflow-y-auto px-6 py-5 space-y-4">
                {{-- Non-phased: single items table --}}
                <div id="opOverallSection">
                    <table class="w-full">
                        <thead class="bg-gray-50 rounded-lg">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 rounded-tl-lg">Item</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500">Required</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500">Completed</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 rounded-tr-lg">Progress</th>
                            </tr>
                        </thead>
                        <tbody id="opOverallBody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                {{-- Phased: per-phase accordion --}}
                <div id="opPhasedSection" class="hidden">
                    <div id="opPhasedContent" class="space-y-4"></div>
                    {{-- Phase Tracking Notes --}}
                    <div id="opTrackingNotesSection" class="hidden mt-4 border-t border-gray-200 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-700">Phase Notes</h4>
                            <span id="opNotesPhaseLabel" class="text-xs text-indigo-600 font-semibold"></span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <textarea id="opPhaseNotesTextarea" rows="3" placeholder="Add tracking notes for this phase..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                            <div class="flex justify-end mt-2">
                                <button onclick="savePhaseNotes()" id="opSaveNotesBtn"
                                    class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                                    Save Notes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button onclick="closeOrderProgressModal()" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Include Modals from pages/Modals --}}
@include('pages.Modals.assignment-details-modal')
@include('pages.Modals.assignment-status-modal')

{{-- Assign Order Items Modal --}}
<div id="assignmentModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAssignmentModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative" onclick="event.stopPropagation()">
            <div class="sticky top-0 bg-white flex items-center justify-between px-6 py-4 border-b border-gray-200 z-10">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Assign Order Items</h3>
                    <p class="text-sm text-gray-500 mt-0.5" id="modalSubtitle">Each product can be assigned to a different employee</p>
                </div>
                <button onclick="closeAssignmentModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-5 space-y-5">
                {{-- Order Select --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Order</label>
                    <select id="orderSelect" onchange="updateOrderPreview()" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Select an order --</option>
                    </select>
                </div>

                {{-- Order Preview --}}
                <div id="orderPreview" class="hidden">
                    {{-- Customer Info --}}
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-5">
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Customer</label>
                                <p id="previewCustomer" class="text-sm font-semibold text-gray-900"></p>
                                <p id="previewContact" class="text-xs text-gray-500"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Delivery Date</label>
                                <p id="previewDeliveryDate" class="text-sm font-medium text-gray-900"></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <p id="previewAddress" class="text-xs text-gray-600"></p>
                        </div>
                    </div>

                    {{-- Per-Item Assignment Table --}}
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-700">Assign Items to Employees</label>
                            <button type="button" onclick="fillAllWithEmployee()" id="fillAllBtn" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium hidden">Fill all with pre-selected</button>
                        </div>
                        <p class="text-xs text-gray-400 mb-3">Assign each product to a different employee — or the same one for all.</p>

                        {{-- Phase Tabs (shown when order has phases) --}}
                        <div id="phaseTabsWrapper" class="hidden mb-3">
                            <div id="phaseTabs" class="flex gap-2 flex-wrap mb-3"></div>
                        </div>

                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[480px]">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Product</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 w-16">Qty</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Assign To Employee</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemAssignmentRows" class="divide-y divide-gray-100">
                                        {{-- Populated via JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <p id="assignValidationMsg" class="text-xs text-red-500 mt-2 hidden">Please assign an employee to every item before continuing.</p>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Assignment Notes (Optional)</label>
                        <textarea id="assignmentNotes" rows="2" placeholder="Add special instructions..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                    </div>

                    {{-- Order Notes --}}
                    <div id="previewNotesSection" class="hidden mt-3 bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <label class="block text-xs font-semibold text-amber-800 uppercase mb-1">Order Notes</label>
                        <p id="previewNotes" class="text-sm text-amber-900"></p>
                    </div>
                </div>
            </div>
            <div class="sticky bottom-0 bg-white flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                <button onclick="closeAssignmentModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button id="assignBtn" disabled onclick="assignOrderToEmployee()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.82m5.84-2.56a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.82m2.56-5.84a14.98 14.98 0 00-12.12 6.16"/></svg>
                    Assign Items
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const assignmentsData = @json($assignmentsData ?? []);
const availableOrders = @json($availableOrders ?? []);
const workersData = @json($workers ?? []);
</script>
@endsection
