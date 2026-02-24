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
                        <button onclick="openAssignDeliveryModal({{ json_encode($dispatch) }})" class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 016.75 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            {{ $dispatch['driver'] === 'Unassigned' ? 'Assign Delivery' : 'Reassign' }}
                        </button>
                        @if($dispatch['status'] === 'pending' && $dispatch['driver'] !== 'Unassigned')
                        <button onclick="updateDispatchStatus({{ $dispatch['id'] }}, 'ship')" class="flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-medium transition-colors">
                            Mark Shipped
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </button>
                        @elseif($dispatch['status'] === 'in_transit')
                        <button onclick="updateDispatchStatus({{ $dispatch['id'] }}, 'deliver')" class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium transition-colors">
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
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Assigned Employee</label>
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

{{-- Assign Delivery Person Modal --}}
<div id="assignDeliveryModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAssignDeliveryModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Assign Employee for Delivery</h3>
                    <p class="text-sm text-gray-500">Order: <span id="assignOrderLabel" class="font-semibold text-emerald-600"></span></p>
                </div>
                <button onclick="closeAssignDeliveryModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-4">
                {{-- Customer & Address Info --}}
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm font-semibold text-gray-900" id="assignCustomerName"></p>
                    <p class="text-xs text-gray-500 mt-1" id="assignAddress"></p>
                    <p class="text-xs text-gray-500 mt-1" id="assignItems"></p>
                </div>

                {{-- Delivery Person Select --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign Employee</label>
                    <select id="deliveryUserSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Select employee...</option>
                        @foreach($deliveryUsers as $du)
                            <option value="{{ $du['id'] }}">{{ $du['name'] }}</option>
                        @endforeach
                    </select>
                    @if(count($deliveryUsers) === 0)
                        <p class="text-xs text-amber-600 mt-1">No employees found. Create one in the Employees page.</p>
                    @endif
                </div>

                {{-- Vehicle --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Vehicle (Optional)</label>
                    <input id="deliveryVehicle" type="text" placeholder="e.g. Motorcycle, Van, Truck"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                <button onclick="closeAssignDeliveryModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    Cancel
                </button>
                <button id="assignDeliveryBtn" onclick="saveDeliveryAssignment()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Assign & Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var currentDispatch = null;
var deliveryUsers = @json($deliveryUsers);

function openAssignDeliveryModal(dispatch) {
    currentDispatch = dispatch;
    document.getElementById('assignOrderLabel').textContent = dispatch.order_id;
    document.getElementById('assignCustomerName').textContent = dispatch.customer;
    document.getElementById('assignAddress').textContent = dispatch.address;
    document.getElementById('assignItems').textContent = dispatch.items || 'No items';

    // Pre-select current driver if already assigned
    var select = document.getElementById('deliveryUserSelect');
    select.value = '';
    for (var i = 0; i < deliveryUsers.length; i++) {
        if (deliveryUsers[i].name === dispatch.driver) {
            select.value = deliveryUsers[i].id;
            break;
        }
    }

    document.getElementById('deliveryVehicle').value = dispatch.vehicle && dispatch.vehicle !== 'Not assigned' ? dispatch.vehicle : '';
    document.getElementById('assignDeliveryModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeAssignDeliveryModal() {
    document.getElementById('assignDeliveryModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentDispatch = null;
}

function saveDeliveryAssignment() {
    if (!currentDispatch) return;
    var userId = document.getElementById('deliveryUserSelect').value;
    if (!userId) {
        alert('Please select an employee.');
        return;
    }

    var btn = document.getElementById('assignDeliveryBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Saving...';

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/dispatch/assign-driver', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            dispatch_id: currentDispatch.id,
            delivery_user_id: userId,
            vehicle: document.getElementById('deliveryVehicle').value || null,
        }),
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            closeAssignDeliveryModal();
            window.location.reload();
        } else {
            alert('Failed to assign delivery person.');
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Assign & Save';
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('An error occurred.');
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Assign & Save';
    });
}

function updateDispatchStatus(dispatchId, action) {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    var label = action === 'ship' ? 'mark as shipped' : 'mark as delivered';
    if (!confirm('Are you sure you want to ' + label + '?')) return;

    fetch('/dispatch/assign-driver', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            dispatch_id: dispatchId,
            action: action,
        }),
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to update status.');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('An error occurred.');
    });
}

function filterDispatch() {
    var search = document.getElementById('dispatchSearch').value.toLowerCase();
    var statusFilter = document.getElementById('dispatchStatusFilter').value;
    var dateFilter = document.getElementById('dispatchDateFilter').value;
    var today = new Date();
    today.setHours(0, 0, 0, 0);

    document.querySelectorAll('.dispatch-card').forEach(function(card) {
        var matchSearch = !search || card.getAttribute('data-search').includes(search);
        var matchStatus = !statusFilter || card.getAttribute('data-status') === statusFilter;

        var matchDate = true;
        if (dateFilter) {
            var cardDate = new Date(card.getAttribute('data-date'));
            cardDate.setHours(0, 0, 0, 0);
            if (dateFilter === 'today') {
                matchDate = cardDate.getTime() === today.getTime();
            } else if (dateFilter === 'week') {
                var weekStart = new Date(today);
                weekStart.setDate(today.getDate() - today.getDay());
                var weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);
                matchDate = cardDate >= weekStart && cardDate <= weekEnd;
            } else if (dateFilter === 'month') {
                matchDate = cardDate.getMonth() === today.getMonth() && cardDate.getFullYear() === today.getFullYear();
            }
        }

        card.style.display = (matchSearch && matchStatus && matchDate) ? '' : 'none';
    });
}
</script>

@endsection
