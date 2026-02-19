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
                <div class="worker-item flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200 transition-all" onclick="showWorkerAssignments('{{ $worker['name'] }}', '{{ $worker['initial'] }}', '{{ $worker['color'] }}', '{{ $worker['status'] }}', {{ $worker['active'] }})">
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
            {{-- Empty State --}}
            <div id="emptyState" class="flex flex-col items-center justify-center h-full min-h-[400px] text-center">
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
</div>

{{-- New Assignment Modal --}}
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
                        <option value="ORD-001">ORD-001 - St. Mary School (500 Data Filer Boxes)</option>
                        <option value="ORD-003">ORD-003 - Office Depot (1000 Storage Boxes)</option>
                        <option value="ORD-004">ORD-004 - Learning Tree (50 Whiteboards)</option>
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

<script>
let currentEmployeeName = '';

const assignmentsData = {
    'Juan Dela Cruz': [],
    'Maria Santos': [
        {
            order_id: 'ORD-002',
            customer: 'City High',
            customer_contact: '0918-234-5678',
            items: '200 Whiteboards',
            delivery_address: '456 Education Ave, Manila',
            delivery_date: '2026-02-22',
            total_amount: 60000,
            priority: 'high',
            status: 'in_progress',
            notes: 'Rush order, fragile items',
            assigned_date: '2026-02-17',
            assigned_by: 'Admin User'
        }
    ],
    'Pedro Reyes': [],
    'Ana Lim': [
        {
            order_id: 'ORD-005',
            customer: 'Gov. Office',
            customer_contact: '0921-567-8901',
            items: '300 Filer Boxes',
            delivery_address: '555 Government Complex, Taguig',
            delivery_date: '2026-03-01',
            total_amount: 15000,
            priority: 'high',
            status: 'in_progress',
            notes: 'Deliver to security office first',
            assigned_date: '2026-02-16',
            assigned_by: 'Admin User'
        }
    ],
    'Luis Torres': []
};

// Available orders for assignment
const availableOrders = [
    {
        order_id: 'ORD-006',
        customer: 'Tech Solutions Inc.',
        customer_contact: '0922-678-9012',
        items: '150 Notebooks',
        delivery_address: '777 Innovation Hub, BGC',
        delivery_date: '2026-02-25',
        total_amount: 45000,
        notes: 'Deliver to HR department'
    },
    {
        order_id: 'ORD-007',
        customer: 'Mega Store',
        customer_contact: '0923-789-0123',
        items: '500 Ballpens, 300 Folders',
        delivery_address: '888 Retail Complex, Alabang',
        delivery_date: '2026-02-24',
        total_amount: 22500,
        notes: 'Bulk order - standard delivery'
    },
    {
        order_id: 'ORD-008',
        customer: 'University of Manila',
        customer_contact: '0924-890-1234',
        items: '100 Whiteboards, 50 Markers',
        delivery_address: '999 Education Drive, Manila',
        delivery_date: '2026-02-28',
        total_amount: 85000,
        notes: 'Rush order for new semester'
    }
];

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
                            <button class="px-3 py-1.5 text-xs font-medium text-emerald-600 bg-white border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-colors">
                                View Full Details
                            </button>
                            <button class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
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
    
    if (!selectedOrderId || !currentEmployeeName) return;
    
    const order = availableOrders.find(o => o.order_id === selectedOrderId);
    if (!order) return;
    
    // Create assignment object
    const assignment = {
        ...order,
        priority: priority,
        status: 'pending',
        assigned_date: new Date().toISOString().split('T')[0],
        assigned_by: 'Admin User' // In real app, this would be the logged-in user
    };
    
    // Add to assignments
    if (!assignmentsData[currentEmployeeName]) {
        assignmentsData[currentEmployeeName] = [];
    }
    assignmentsData[currentEmployeeName].push(assignment);
    
    // Remove from available orders
    const index = availableOrders.findIndex(o => o.order_id === selectedOrderId);
    if (index > -1) {
        availableOrders.splice(index, 1);
    }
    
    // Refresh the display if this worker is currently selected
    const workerAssignments = document.getElementById('workerAssignments');
    if (!workerAssignments.classList.contains('hidden')) {
        // Find the worker's status and details to refresh
        const workerName = document.getElementById('workerName').textContent;
        if (workerName === currentEmployeeName) {
            // Get worker details from the button
            const workerButtons = document.querySelectorAll('[onclick^="showWorkerAssignments"]');
            workerButtons.forEach(button => {
                if (button.textContent.includes(currentEmployeeName)) {
                    button.click();
                }
            });
        }
    }
    
    // Close modal
    closeAssignmentModal();
    
    // Show success message
    alert(`Order ${selectedOrderId} successfully assigned to ${currentEmployeeName}!`);
}

</script>
@endsection
