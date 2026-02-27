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
                @foreach($workers as $worker)
                <div class="worker-item flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200 transition-all" onclick="showWorkerAssignments('{{ $worker['name'] }}', '{{ $worker['initial'] }}', '{{ $worker['color'] }}', '{{ $worker['status'] }}', {{ $worker['active'] }})">
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

    {{-- Available Orders Section --}}
    <div class="mt-6 bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                    </svg>
                    Available Orders
                </h3>
                <p class="text-sm text-gray-500">Unassigned orders ready to be assigned to employees</p>
            </div>
            <span id="availableOrderCount" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-700">{{ count($availableOrders) }} orders</span>
        </div>

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
                {{-- Order Header --}}
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

                {{-- Order Details --}}
                <div class="space-y-2 mb-3">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                        <span class="truncate" title="{{ $order['items'] }}">{{ Str::limit($order['items'], 40) }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/>
                        </svg>
                        Deliver by {{ \Carbon\Carbon::parse($order['delivery_date'])->format('M d, Y') }}
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        <span class="truncate" title="{{ $order['delivery_address'] }}">{{ Str::limit($order['delivery_address'], 35) }}</span>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <p class="text-sm font-bold text-emerald-600">₱{{ number_format((float)$order['total_amount'], 2) }}</p>
                    <button onclick="quickAssignOrder('{{ $order['order_id'] }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors opacity-0 group-hover:opacity-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Assign
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Include Modals from pages/Modals --}}
@include('pages.Modals.assignment-details-modal')
@include('pages.Modals.assignment-status-modal')
<div id="assignmentModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAssignmentModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative" onclick="event.stopPropagation()">
            <div class="sticky top-0 bg-white flex items-center justify-between px-6 py-4 border-b border-gray-200 z-10">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Assign Order to Employee</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Select an order to assign to <span id="modalEmployeeName" class="font-medium text-gray-700"></span></p>
                </div>
                <button onclick="closeAssignmentModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-5">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Order</label>
                    <select id="orderSelect" onchange="updateOrderPreview()" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Select an order --</option>
                    </select>
                </div>

                {{-- Order Preview --}}
                <div id="orderPreview" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Order Details</label>
                    <div class="border-2 border-emerald-200 rounded-xl p-5 bg-emerald-50/30">
                        <div class="grid grid-cols-2 gap-4 mb-4">
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
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Delivery Address</label>
                            <p id="previewAddress" class="text-sm text-gray-700"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Order Items</label>
                                <p id="previewItems" class="text-sm font-medium text-gray-900"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Total Amount</label>
                                <p id="previewAmount" class="text-sm font-bold text-emerald-600"></p>
                            </div>
                        </div>
                        <div id="previewNotesSection" class="hidden bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <label class="block text-xs font-semibold text-amber-800 uppercase mb-1">Important Notes</label>
                            <p id="previewNotes" class="text-sm text-amber-900"></p>
                        </div>
                    </div>

                    {{-- Additional Assignment Options --}}
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority Level</label>
                            <select id="assignmentPriority" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Assignment Notes (Optional)</label>
                            <textarea id="assignmentNotes" rows="2" placeholder="Add special instructions for this employee..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sticky bottom-0 bg-white flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                <button onclick="closeAssignmentModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button id="assignBtn" disabled onclick="assignOrderToEmployee()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Assign Order
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Quick Assign Modal (Worker Picker) --}}
<div id="quickAssignModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeQuickAssignModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Quick Assign</h3>
                    <p class="text-sm text-gray-500">Assign <span id="quickAssignOrderLabel" class="font-semibold text-emerald-600"></span> to an employee</p>
                </div>
                <button onclick="closeQuickAssignModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                <div class="space-y-1" id="quickAssignWorkerList">
                    {{-- Populated dynamically --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include Modals from pages/Modals --}}
@include('pages.Modals.assignment-details-modal')
@include('pages.Modals.assignment-status-modal')

<script>
let currentEmployeeName = '';
let quickAssignOrderId = '';
let currentDetailAssignment = null;
let currentUpdateAssignment = null;
let selectedNewStatus = '';

const assignmentsData = @json($assignmentsData ?? []);

// Available orders for assignment
const availableOrders = @json($availableOrders ?? []);

// Workers data for employee lookup
const workersData = @json($workers ?? []);

function showWorkerAssignments(name, initial, color, status, activeCount) {
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('workerAssignments').classList.remove('hidden');
    
    document.getElementById('workerAvatar').textContent = initial;
    document.getElementById('workerAvatar').className = `w-12 h-12 rounded-full ${color} flex items-center justify-center text-white font-bold`;
    document.getElementById('workerName').textContent = name;
    
    const statusColors = {
        'AVAILABLE': 'bg-green-100 text-green-700',
        'BUSY': 'bg-amber-100 text-amber-700',
        'OFFLINE': 'bg-gray-100 text-gray-500'
    };
    document.getElementById('workerStatus').textContent = status;
    document.getElementById('workerStatus').className = `inline-flex px-2 py-0.5 rounded text-xs font-semibold ${statusColors[status]}`;
    
    const assignments = assignmentsData[name] || [];
    const assignmentsList = document.getElementById('assignmentsList');
    
    if (assignments.length === 0) {
        assignmentsList.innerHTML = `
            <div class="text-center py-12">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                </svg>
                <p class="text-gray-500 text-sm font-medium">No orders assigned yet</p>
                <p class="text-gray-400 text-xs mt-1">Orders assigned to this employee will appear here</p>
            </div>
        `;
    } else {
        assignmentsList.innerHTML = assignments.map(order => {
            const priorityColors = {
                'normal': 'bg-emerald-50 text-emerald-600 border-emerald-200',
                'high': 'bg-orange-50 text-orange-600 border-orange-200',
                'urgent': 'bg-red-50 text-red-600 border-red-200'
            };
            
            const statusColors = {
                'pending': 'bg-gray-50 text-gray-600 border-gray-200',
                'in_progress': 'bg-blue-50 text-blue-600 border-blue-200',
                'completed': 'bg-green-50 text-green-600 border-green-200',
                'cancelled': 'bg-red-50 text-red-600 border-red-200'
            };
            
            return `
                <div class="border-2 border-emerald-200 rounded-xl p-5 bg-emerald-50/30 hover:shadow-lg transition-all">
                    {{-- Order Header --}}
                    <div class="flex items-start justify-between mb-4 pb-3 border-b border-emerald-200">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-emerald-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900">${order.order_id}</h4>
                                <p class="text-xs text-gray-500">Assigned ${new Date(order.assigned_date).toLocaleDateString()}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border ${priorityColors[order.priority] || priorityColors['normal']}">${order.priority.toUpperCase()}</span>
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border ${statusColors[order.status]}">${order.status.replace('_', ' ').toUpperCase()}</span>
                        </div>
                    </div>

                    {{-- Customer Info --}}
                    <div class="bg-white rounded-lg p-4 mb-3">
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Customer</label>
                                <p class="text-sm font-semibold text-gray-900">${order.customer}</p>
                                <p class="text-xs text-gray-500">${order.customer_contact}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Delivery Date</label>
                                <div class="flex items-center gap-1 text-sm font-medium text-gray-900">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/>
                                    </svg>
                                    ${new Date(order.delivery_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Delivery Address</label>
                            <div class="flex items-start gap-1 text-sm text-gray-700">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                                ${order.delivery_address}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Order Items</label>
                                <p class="text-sm font-medium text-gray-900">${order.items}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Total Amount</label>
                                <p class="text-sm font-bold text-emerald-600">₱${order.total_amount.toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    ${order.notes ? `
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-amber-800 uppercase mb-0.5">Important Notes</p>
                                    <p class="text-sm text-amber-900">${order.notes}</p>
                                </div>
                            </div>
                        </div>
                    ` : ''}

                    {{-- Footer --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                        <p class="text-xs text-gray-500">Assigned by: <span class="font-medium text-gray-700">${order.assigned_by}</span></p>
                        <div class="flex gap-2">
                            <button onclick="viewAssignmentDetails(${JSON.stringify(order).replace(/"/g, '&quot;')})" class="px-3 py-1.5 text-xs font-medium text-emerald-600 bg-white border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-colors">
                                View Full Details
                            </button>
                            <button onclick="openUpdateStatusModal(${JSON.stringify(order).replace(/"/g, '&quot;')})" class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                                Update Status
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
}

function openAssignmentModal(employeeName) {
    currentEmployeeName = employeeName;
    document.getElementById('assignmentModal').classList.remove('hidden');
    document.getElementById('modalEmployeeName').textContent = employeeName;
    
    // Populate order dropdown
    const orderSelect = document.getElementById('orderSelect');
    orderSelect.innerHTML = '<option value="">Select an order...</option>' + 
        availableOrders.map(order => 
            `<option value="${order.order_id}">${order.order_id} - ${order.customer}</option>`
        ).join('');
    
    // Reset form
    document.getElementById('orderPreview').classList.add('hidden');
    document.getElementById('assignBtn').disabled = true;
}

function closeAssignmentModal() {
    document.getElementById('assignmentModal').classList.add('hidden');
    currentEmployeeName = '';
}

function updateOrderPreview() {
    const selectedOrderId = document.getElementById('orderSelect').value;
    const orderPreview = document.getElementById('orderPreview');
    
    if (!selectedOrderId) {
        orderPreview.classList.add('hidden');
        document.getElementById('assignBtn').disabled = true;
        return;
    }
    
    const order = availableOrders.find(o => o.order_id === selectedOrderId);
    if (!order) return;
    
    // Show preview
    orderPreview.classList.remove('hidden');
    document.getElementById('assignBtn').disabled = false;
    
    // Update preview content
    document.getElementById('previewCustomer').textContent = order.customer;
    document.getElementById('previewContact').textContent = order.customer_contact;
    document.getElementById('previewItems').textContent = order.items;
    document.getElementById('previewAddress').textContent = order.delivery_address;
    document.getElementById('previewDeliveryDate').textContent = new Date(order.delivery_date).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
    document.getElementById('previewAmount').textContent = '₱' + order.total_amount.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('previewNotes').textContent = order.notes || 'No special notes';
}

function assignOrderToEmployee() {
    const selectedOrderId = document.getElementById('orderSelect').value;
    const priority = document.getElementById('assignmentPriority').value;
    const notes = document.getElementById('assignmentNotes').value;
    
    if (!selectedOrderId || !currentEmployeeName) return;
    
    // Find employee_id from workers data
    const worker = workersData.find(w => w.name === currentEmployeeName);
    if (!worker) return;
    
    const assignBtn = document.getElementById('assignBtn');
    assignBtn.disabled = true;
    assignBtn.textContent = 'Assigning...';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch('/assignments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            order_id: selectedOrderId,
            employee_id: worker.id,
            priority: priority,
            notes: notes || null,
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAssignmentModal();
            showToast(`Order ${selectedOrderId} successfully assigned to ${currentEmployeeName}!`, 'success');
            // Reload page to refresh all data
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast(data.message || 'Failed to assign order. Please try again.', 'error');
            assignBtn.disabled = false;
            assignBtn.textContent = 'Assign Order';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        assignBtn.disabled = false;
        assignBtn.textContent = 'Assign Order';
    });
}

function quickAssignOrder(orderId) {
    // Open a worker picker for quick assignment
    const workerNames = workersData.map(w => w.name);
    if (workerNames.length === 0) {
        showToast('No employees available for assignment.', 'error');
        return;
    }
    
    // Set the order, then open the quick assign modal
    quickAssignOrderId = orderId;
    
    const pickerHtml = workerNames.map(name => {
        const worker = workersData.find(w => w.name === name);
        const activeCount = worker ? worker.active : 0;
        const onClickAttr = `onclick="confirmQuickAssign('${name}')"`;
        const statusNote = `<span class="text-xs text-gray-500">${activeCount} active assignment${activeCount !== 1 ? 's' : ''}</span>`;
        return `
            <button ${onClickAttr} class="flex items-center justify-between w-full p-3 rounded-lg hover:bg-emerald-50 border border-transparent hover:border-emerald-200 transition-all text-left">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full ${worker.color} flex items-center justify-center text-white font-bold text-xs">${worker.initial}</div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">${name}</p>
                        ${statusNote}
                    </div>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </button>
        `;
    }).join('');
    
    document.getElementById('quickAssignOrderLabel').textContent = orderId;
    document.getElementById('quickAssignWorkerList').innerHTML = pickerHtml;
    document.getElementById('quickAssignModal').classList.remove('hidden');
}

function closeQuickAssignModal() {
    document.getElementById('quickAssignModal').classList.add('hidden');
    quickAssignOrderId = '';
}

function confirmQuickAssign(employeeName) {
    if (!quickAssignOrderId) return;
    
    const worker = workersData.find(w => w.name === employeeName);
    if (!worker) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Disable all buttons in the modal
    document.querySelectorAll('#quickAssignWorkerList button').forEach(btn => btn.disabled = true);
    
    fetch('/assignments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            order_id: quickAssignOrderId,
            employee_id: worker.id,
            priority: 'normal',
            notes: null,
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeQuickAssignModal();
            showToast(`Order ${quickAssignOrderId} assigned to ${employeeName}!`, 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast('Failed to assign order. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// ========== View Full Details Modal ==========
function viewAssignmentDetails(order) {
    currentDetailAssignment = order;
    
    document.getElementById('detailOrderId').textContent = order.order_id;
    document.getElementById('detailAssignedDate').textContent = 'Assigned on ' + new Date(order.assigned_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    
    // Priority badge
    var priorityColors = {
        'normal': 'bg-emerald-50 text-emerald-600 border-emerald-200',
        'high': 'bg-orange-50 text-orange-600 border-orange-200',
        'urgent': 'bg-red-50 text-red-600 border-red-200'
    };
    var prBadge = document.getElementById('detailPriorityBadge');
    prBadge.textContent = order.priority.toUpperCase();
    prBadge.className = 'inline-flex px-3 py-1 rounded-full text-xs font-semibold border ' + (priorityColors[order.priority] || priorityColors['normal']);
    
    // Status badge
    var statusColors = {
        'pending': 'bg-gray-50 text-gray-600 border-gray-200',
        'in_progress': 'bg-blue-50 text-blue-600 border-blue-200',
        'completed': 'bg-green-50 text-green-600 border-green-200',
        'cancelled': 'bg-red-50 text-red-600 border-red-200'
    };
    var stBadge = document.getElementById('detailStatusBadge');
    stBadge.textContent = order.status.replace('_', ' ').toUpperCase();
    stBadge.className = 'inline-flex px-3 py-1 rounded-full text-xs font-semibold border ' + (statusColors[order.status] || statusColors['pending']);
    
    // Customer info
    document.getElementById('detailCustomer').textContent = order.customer;
    document.getElementById('detailContact').textContent = order.customer_contact || 'N/A';
    
    // Delivery info
    document.getElementById('detailDeliveryDate').textContent = new Date(order.delivery_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    document.getElementById('detailTotalAmount').textContent = '₱' + parseFloat(order.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('detailAddress').textContent = order.delivery_address || 'N/A';
    
    // Items
    document.getElementById('detailItems').textContent = order.items || 'N/A';
    
    // Notes
    var notesSection = document.getElementById('detailNotesSection');
    if (order.notes) {
        notesSection.classList.remove('hidden');
        document.getElementById('detailNotes').textContent = order.notes;
    } else {
        notesSection.classList.add('hidden');
    }
    
    // Metadata
    document.getElementById('detailAssignedBy').textContent = 'Assigned by: ' + order.assigned_by;
    document.getElementById('detailAssignmentId').textContent = 'Assignment #' + order.id;
    
    document.getElementById('assignmentDetailsModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeAssignmentDetailsModal() {
    document.getElementById('assignmentDetailsModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentDetailAssignment = null;
}

// ========== Update Status Modal ==========
function openUpdateStatusModal(order) {
    currentUpdateAssignment = order;
    selectedNewStatus = order.status;
    
    document.getElementById('statusOrderLabel').textContent = order.order_id;
    
    // Set current status badge
    var statusColors = {
        'pending': 'bg-gray-50 text-gray-600 border-gray-200',
        'in_progress': 'bg-blue-50 text-blue-600 border-blue-200',
        'completed': 'bg-green-50 text-green-600 border-green-200',
        'cancelled': 'bg-red-50 text-red-600 border-red-200'
    };
    var currentBadge = document.getElementById('currentStatusBadge');
    currentBadge.textContent = order.status.replace('_', ' ').toUpperCase();
    currentBadge.className = 'inline-flex px-3 py-1.5 rounded-full text-sm font-semibold border ' + (statusColors[order.status] || statusColors['pending']);
    
    // Set notes
    document.getElementById('updateNotes').value = order.notes || '';
    
    // Highlight current status option
    selectStatus(order.status);
    
    document.getElementById('updateStatusModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeUpdateStatusModal() {
    document.getElementById('updateStatusModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentUpdateAssignment = null;
    selectedNewStatus = '';
}

function selectStatus(status) {
    selectedNewStatus = status;
    var borderColors = {
        'pending': 'border-gray-500 bg-gray-50',
        'in_progress': 'border-blue-500 bg-blue-50',
        'completed': 'border-green-500 bg-green-50',
        'cancelled': 'border-red-500 bg-red-50'
    };
    
    document.querySelectorAll('.status-option').forEach(function(btn) {
        btn.className = 'status-option flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-gray-400 transition-all';
    });
    
    var selected = document.querySelector('.status-option[data-status="' + status + '"]');
    if (selected) {
        selected.className = 'status-option flex items-center gap-2 p-3 border-2 rounded-xl text-sm font-semibold transition-all ' + (borderColors[status] || 'border-gray-500 bg-gray-50');
    }
}

function saveStatusUpdate() {
    if (!currentUpdateAssignment || !selectedNewStatus) return;
    
    var saveBtn = document.getElementById('saveStatusBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Saving...';
    
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch('/assignments/' + currentUpdateAssignment.id, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            status: selectedNewStatus,
            notes: document.getElementById('updateNotes').value || null,
        }),
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            closeUpdateStatusModal();
            showToast('Assignment status updated successfully!', 'success');
            setTimeout(function() { window.location.reload(); }, 800);
        } else {
            showToast('Failed to update status. Please try again.', 'error');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Save Changes';
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Save Changes';
    });
}

function showToast(message, type) {
    const existing = document.getElementById('assignmentToast');
    if (existing) existing.remove();
    
    const bgColor = type === 'success' ? 'bg-emerald-600' : 'bg-red-600';
    const toast = document.createElement('div');
    toast.id = 'assignmentToast';
    toast.className = `fixed top-6 right-6 z-[100] ${bgColor} text-white px-5 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 animate-fade-in`;
    toast.innerHTML = `
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            ${type === 'success' 
                ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>'
            }
        </svg>
        ${message}
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

</script>
@endsection
