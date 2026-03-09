// Assignments Page JavaScript
// ============================
// All assignments page functions.
// PHP data injections (_unassignedCount, _activeCount, _activeOrdersData,
// assignmentsData, availableOrders, workersData) are provided by the blade template.

// ========== Order Progress Modal ==========
window.openOrderProgressModal = function(orderId) {
    const order = _activeOrdersData[orderId];
    if (!order) return;

    document.getElementById('opModalTitle').textContent = orderId;
    document.getElementById('opModalCustomer').textContent = order.customer + ' · ' + new Date(order.delivery_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    const hasMeaningfulPhases = order.phases_progress && order.phases_progress.length > 0;
    const phasedSection  = document.getElementById('opPhasedSection');
    const overallSection = document.getElementById('opOverallSection');

    if (hasMeaningfulPhases) {
        phasedSection.classList.remove('hidden');
        overallSection.classList.add('hidden');
        let html = '';
        order.phases_progress.forEach(function(phase) {
            const statusBadge = {
                'Completed':   'bg-green-50 text-green-600',
                'In-Progress': 'bg-blue-50 text-blue-600',
                'Pending':     'bg-gray-100 text-gray-500',
                'Delivered':   'bg-emerald-50 text-emerald-600',
            }[phase.status] || 'bg-gray-100 text-gray-500';
            const delivDate = new Date(phase.delivery_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const rows = phase.items.map(function(item) {
                const pct = item.required_qty > 0 ? Math.round((item.completed_qty / item.required_qty) * 100) : 0;
                const barColor = pct >= 100 ? 'bg-emerald-500' : pct > 0 ? 'bg-blue-400' : 'bg-gray-200';
                return `<tr class="border-t border-gray-100">
                    <td class="px-4 py-2.5 text-sm text-gray-800">${item.name}</td>
                    <td class="px-4 py-2.5 text-sm text-center font-medium text-gray-700">${item.required_qty}</td>
                    <td class="px-4 py-2.5 text-sm text-center font-medium text-gray-700">${item.completed_qty}</td>
                    <td class="px-4 py-2.5">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="${barColor} h-full rounded-full transition-all" style="width:${pct}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-gray-500 w-9 text-right">${pct}%</span>
                        </div>
                    </td>
                </tr>`;
            }).join('');
            html += `<div class="mb-4">
                <div class="flex items-center justify-between px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-t-lg">
                    <span class="text-xs font-bold text-indigo-700 uppercase">Phase ${phase.number}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">${delivDate}</span>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold ${statusBadge}">${phase.status}</span>
                    </div>
                </div>
                <div class="border border-t-0 border-indigo-200 rounded-b-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Item</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500">Required</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500">Completed</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Progress</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>`;
        });
        document.getElementById('opPhasedContent').innerHTML = html;
    } else {
        phasedSection.classList.add('hidden');
        overallSection.classList.remove('hidden');
        const rows = (order.progress_items || []).map(function(item) {
            const pct = item.required_qty > 0 ? Math.round((item.completed_qty / item.required_qty) * 100) : 0;
            const barColor = pct >= 100 ? 'bg-emerald-500' : pct > 0 ? 'bg-blue-400' : 'bg-gray-200';
            return `<tr class="border-t border-gray-100">
                <td class="px-4 py-2.5 text-sm text-gray-800">${item.name}</td>
                <td class="px-4 py-2.5 text-sm text-center font-medium text-gray-700">${item.required_qty}</td>
                <td class="px-4 py-2.5 text-sm text-center font-medium text-gray-700">${item.completed_qty}</td>
                <td class="px-4 py-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="${barColor} h-full rounded-full transition-all" style="width:${pct}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 w-9 text-right">${pct}%</span>
                    </div>
                </td>
            </tr>`;
        }).join('');
        document.getElementById('opOverallBody').innerHTML = rows || '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 text-sm">No items found</td></tr>';
    }

    document.getElementById('orderProgressModal').classList.remove('hidden');
}

window.closeOrderProgressModal = function() {
    document.getElementById('orderProgressModal').classList.add('hidden');
}

window.switchOrdersTab = function(tab) {
    const isUnassigned = tab === 'unassigned';
    document.getElementById('panelUnassigned').classList.toggle('hidden', !isUnassigned);
    document.getElementById('panelActive').classList.toggle('hidden', isUnassigned);

    const tabUnassigned = document.getElementById('tabUnassigned');
    const tabActive = document.getElementById('tabActive');
    if (isUnassigned) {
        tabUnassigned.className = 'px-3 py-1.5 text-xs font-semibold rounded-md bg-white text-emerald-700 shadow-sm transition-all';
        tabActive.className = 'px-3 py-1.5 text-xs font-semibold rounded-md text-gray-500 hover:text-gray-700 transition-all';
    } else {
        tabActive.className = 'px-6 py-1.5 text-xs font-semibold rounded-md bg-white text-blue-600 shadow-sm transition-all';
        tabUnassigned.className = 'px-3 py-1.5 text-xs font-semibold rounded-md text-gray-500 hover:text-gray-700 transition-all';
    }

    document.getElementById('ordersSectionTitle').textContent = isUnassigned ? 'Unassigned Orders' : 'Active Orders';
    document.getElementById('ordersSectionSubtitle').textContent = isUnassigned
        ? 'Unassigned orders ready to be assigned to employees'
        : 'Orders currently in progress or pending';
    document.getElementById('ordersTabCount').textContent = (isUnassigned ? _unassignedCount : _activeCount) + ' orders';
    document.getElementById('ordersTabCount').className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold '
        + (isUnassigned ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700');
}

// ========== Worker Assignments Panel ==========
let currentEmployeeName = '';
let prefillEmployeeId = null;
let currentDetailAssignment = null;
let currentUpdateAssignment = null;
let selectedNewStatus = '';

window.showWorkerAssignments = function(name, initial, color, status, activeCount) {
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
                    <div class="flex items-start justify-between mb-4 pb-3 border-b border-emerald-200">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-emerald-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900">${order.order_id}</h4>
                                ${order.assigned_item ? `<span class="inline-flex items-center gap-1 mt-0.5 px-2 py-0.5 bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-semibold rounded-full"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3"/></svg>${order.assigned_item}</span>` : `<p class="text-xs text-gray-500">Assigned ${new Date(order.assigned_date).toLocaleDateString()}</p>`}
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border ${priorityColors[order.priority] || priorityColors['normal']}">${order.priority.toUpperCase()}</span>
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border ${statusColors[order.status]}">${order.status.replace('_', ' ').toUpperCase()}</span>
                        </div>
                    </div>

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

// ========== Assignment Modal ==========
window.openAssignmentModal = function(employeeName) {
    currentEmployeeName = employeeName || '';
    prefillEmployeeId = null;

    if (employeeName) {
        const worker = workersData.find(w => w.name === employeeName);
        if (worker) prefillEmployeeId = worker.id;
        document.getElementById('modalSubtitle').textContent =
            `Items not assigned to ${employeeName} will be pre-filled — you can change any.`;
        document.getElementById('fillAllBtn') && document.getElementById('fillAllBtn').classList.remove('hidden');
    } else {
        document.getElementById('modalSubtitle').textContent = 'Assign each product to an employee — different workers can handle different items.';
        document.getElementById('fillAllBtn') && document.getElementById('fillAllBtn').classList.add('hidden');
    }

    // Populate order dropdown
    const orderSelect = document.getElementById('orderSelect');
    orderSelect.innerHTML = '<option value="">-- Select an order --</option>' +
        availableOrders.map(o =>
            `<option value="${o.order_id}">${o.order_id} — ${o.customer}</option>`
        ).join('');

    document.getElementById('orderPreview').classList.add('hidden');
    document.getElementById('assignBtn').disabled = true;
    document.getElementById('assignmentModal').classList.remove('hidden');
}

window.closeAssignmentModal = function() {
    document.getElementById('assignmentModal').classList.add('hidden');
    currentEmployeeName = '';
    prefillEmployeeId = null;
}

window.updateOrderPreview = function() {
    const selectedOrderId = document.getElementById('orderSelect').value;
    const orderPreview = document.getElementById('orderPreview');

    if (!selectedOrderId) {
        orderPreview.classList.add('hidden');
        document.getElementById('assignBtn').disabled = true;
        return;
    }

    const order = availableOrders.find(o => o.order_id === selectedOrderId);
    if (!order) return;

    // Customer info
    document.getElementById('previewCustomer').textContent = order.customer;
    document.getElementById('previewContact').textContent = order.customer_contact || '';
    document.getElementById('previewDeliveryDate').textContent = new Date(order.delivery_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('previewAddress').textContent = order.delivery_address || '';

    // Order notes
    const notesSection = document.getElementById('previewNotesSection');
    if (order.notes) {
        notesSection.classList.remove('hidden');
        document.getElementById('previewNotes').textContent = order.notes;
    } else {
        notesSection.classList.add('hidden');
    }

    // Build per-item assignment rows
    const employeeOptions = workersData.map(w =>
        `<option value="${w.id}" ${prefillEmployeeId === w.id ? 'selected' : ''}>${w.name}${w.active > 0 ? ' (' + w.active + ' active)' : ''}</option>`
    ).join('');

    // selectedEmployeesPerItem: { [itemId]: [{id, name}, ...] }
    window.selectedEmployeesPerItem = {};

    const phases = order.phases || [];
    const phaseTabsWrapper = document.getElementById('phaseTabsWrapper');
    const phaseTabs = document.getElementById('phaseTabs');

    if (phases.length > 0) {
        // --- Phase-tabbed mode ---
        phaseTabsWrapper.classList.remove('hidden');
        window._currentPhases = phases;
        window._activePhaseIndex = 0;

        // pre-init selectedEmployeesPerItem for all items across all phases
        phases.forEach(phase => {
            phase.items.forEach(item => {
                if (!window.selectedEmployeesPerItem[item.id]) {
                    window.selectedEmployeesPerItem[item.id] = [];
                    if (prefillEmployeeId) {
                        const emp = workersData.find(w => w.id === prefillEmployeeId);
                        if (emp) window.selectedEmployeesPerItem[item.id] = [{ id: emp.id, name: emp.name }];
                    }
                }
            });
        });

        function renderPhaseTabs() {
            phaseTabs.innerHTML = phases.map((phase, i) => {
                const date = new Date(phase.delivery_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                const isActive = i === window._activePhaseIndex;
                return `<button type="button" onclick="window._activePhaseIndex=${i}; renderPhaseItems();"
                    class="px-4 py-2 rounded-lg text-xs font-semibold border transition-colors ${isActive
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400 hover:text-indigo-600'}">
                    Phase ${phase.number} <span class="opacity-70 ml-1">${date}</span>
                </button>`;
            }).join('');
        }

        window.renderPhaseItems = function() {
            renderPhaseTabs();
            const phase = phases[window._activePhaseIndex];
            const rowsHtml = (phase.items || []).map(item => {
                return `
                <tr class="item-assign-row" data-item-id="${item.id}">
                    <td class="px-4 py-3 align-top">
                        <p class="text-sm font-semibold text-gray-900">${item.name}</p>
                        <p class="text-xs text-gray-400">₱${item.price.toLocaleString('en-US', {minimumFractionDigits: 2})} / unit</p>
                    </td>
                    <td class="px-4 py-3 text-center align-top">
                        <span class="text-sm font-bold text-gray-900">${item.required_qty}</span>
                    </td>
                    <td class="px-4 py-3 align-top">
                        <div class="flex flex-wrap gap-1.5 mb-2 emp-tags-container" id="tags-${item.id}"></div>
                        <div class="flex gap-2">
                            <select class="item-emp-add-select flex-1 px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    data-item-id="${item.id}">
                                <option value="">+ Add employee</option>
                                ${employeeOptions}
                            </select>
                            <button type="button" onclick="addEmployeeToItem(${item.id})"
                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition-colors whitespace-nowrap">
                                Add
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
            document.getElementById('itemAssignmentRows').innerHTML = rowsHtml;
            (phase.items || []).forEach(item => renderItemTags(item.id));
            checkAllAssigned();
        };

        renderPhaseTabs();
        window.renderPhaseItems();

    } else {
        // --- No phases: flat items mode ---
        phaseTabsWrapper.classList.add('hidden');
        window._currentPhases = [];

        const rowsHtml = (order.order_items || []).map(item => {
            window.selectedEmployeesPerItem[item.id] = [];
            if (prefillEmployeeId) {
                const emp = workersData.find(w => w.id === prefillEmployeeId);
                if (emp) window.selectedEmployeesPerItem[item.id] = [{ id: emp.id, name: emp.name }];
            }
            return `
            <tr class="item-assign-row" data-item-id="${item.id}">
                <td class="px-4 py-3 align-top">
                    <p class="text-sm font-semibold text-gray-900">${item.name}</p>
                    <p class="text-xs text-gray-400">₱${item.price.toLocaleString('en-US', {minimumFractionDigits: 2})} / unit</p>
                </td>
                <td class="px-4 py-3 text-center align-top">
                    <span class="text-sm font-bold text-gray-900">${item.quantity}</span>
                </td>
                <td class="px-4 py-3 align-top">
                    <div class="flex flex-wrap gap-1.5 mb-2 emp-tags-container" id="tags-${item.id}"></div>
                    <div class="flex gap-2">
                        <select class="item-emp-add-select flex-1 px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                data-item-id="${item.id}">
                            <option value="">+ Add employee</option>
                            ${employeeOptions}
                        </select>
                        <button type="button" onclick="addEmployeeToItem(${item.id})"
                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition-colors whitespace-nowrap">
                            Add
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        document.getElementById('itemAssignmentRows').innerHTML = rowsHtml;
        (order.order_items || []).forEach(item => renderItemTags(item.id));
    }

    document.getElementById('assignValidationMsg') && document.getElementById('assignValidationMsg').classList.add('hidden');
    orderPreview.classList.remove('hidden');
    checkAllAssigned();
}

window.addEmployeeToItem = function(itemId) {
    const sel = document.querySelector(`.item-emp-add-select[data-item-id="${itemId}"]`);
    if (!sel || !sel.value) return;
    const empId = parseInt(sel.value);
    const empName = sel.options[sel.selectedIndex].text;

    if (!window.selectedEmployeesPerItem[itemId]) window.selectedEmployeesPerItem[itemId] = [];
    // Prevent duplicates
    if (window.selectedEmployeesPerItem[itemId].find(e => e.id === empId)) {
        sel.value = '';
        return;
    }
    window.selectedEmployeesPerItem[itemId].push({ id: empId, name: empName });
    sel.value = '';
    renderItemTags(itemId);
    checkAllAssigned();
}

window.removeEmployeeFromItem = function(itemId, empId) {
    if (!window.selectedEmployeesPerItem[itemId]) return;
    window.selectedEmployeesPerItem[itemId] = window.selectedEmployeesPerItem[itemId].filter(e => e.id !== empId);
    renderItemTags(itemId);
    checkAllAssigned();
}

window.renderItemTags = function(itemId) {
    const container = document.getElementById('tags-' + itemId);
    if (!container) return;
    const emps = window.selectedEmployeesPerItem[itemId] || [];
    container.innerHTML = emps.map(e =>
        `<span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-medium rounded-full">
            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[9px] font-bold flex-none">${e.name.charAt(0).toUpperCase()}</span>
            ${e.name}
            <button type="button" onclick="removeEmployeeFromItem(${itemId}, ${e.id})" class="ml-0.5 text-emerald-500 hover:text-red-500 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </span>`
    ).join('');
}

window.checkAllAssigned = function() {
    if (!window.selectedEmployeesPerItem) { document.getElementById('assignBtn').disabled = true; return; }

    let itemIds = [];
    if (window._currentPhases && window._currentPhases.length > 0) {
        // Only check items in the currently active phase
        const phase = window._currentPhases[window._activePhaseIndex || 0];
        (phase.items || []).forEach(item => {
            if (!itemIds.includes(item.id)) itemIds.push(item.id);
        });
    } else {
        document.querySelectorAll('.item-assign-row').forEach(row => itemIds.push(parseInt(row.dataset.itemId)));
    }

    const allHaveAtLeastOne = itemIds.length > 0 && itemIds.every(id =>
        (window.selectedEmployeesPerItem[id] || []).length > 0
    );
    document.getElementById('assignBtn').disabled = !allHaveAtLeastOne;
}

window.fillAllWithEmployee = function() {
    if (!prefillEmployeeId) return;
    const emp = workersData.find(w => w.id === prefillEmployeeId);
    if (!emp) return;
    document.querySelectorAll('.item-assign-row').forEach(row => {
        const itemId = parseInt(row.dataset.itemId);
        if (!window.selectedEmployeesPerItem[itemId]) window.selectedEmployeesPerItem[itemId] = [];
        if (!window.selectedEmployeesPerItem[itemId].find(e => e.id === emp.id)) {
            window.selectedEmployeesPerItem[itemId].push({ id: emp.id, name: emp.name });
        }
        renderItemTags(itemId);
    });
    checkAllAssigned();
}

window.assignOrderToEmployee = function() {
    const selectedOrderId = document.getElementById('orderSelect').value;
    if (!selectedOrderId) return;

    // Build flat item_assignments array (one entry per employee per item, for active phase only)
    const itemAssignments = [];
    if (window._currentPhases && window._currentPhases.length > 0) {
        // Phase mode: only create assignments for the currently active/selected phase
        const phase = window._currentPhases[window._activePhaseIndex || 0];
        (phase.items || []).forEach(item => {
            (window.selectedEmployeesPerItem[item.id] || []).forEach(emp => {
                itemAssignments.push({
                    order_item_id: item.id,
                    phase_id:      phase.phase_id,
                    employee_id:   emp.id,
                });
            });
        });
    } else {
        document.querySelectorAll('.item-assign-row').forEach(row => {
            const itemId = parseInt(row.dataset.itemId);
            (window.selectedEmployeesPerItem[itemId] || []).forEach(emp => {
                itemAssignments.push({
                    order_item_id: itemId,
                    employee_id: emp.id,
                });
            });
        });
    }

    const order = availableOrders.find(o => o.order_id === selectedOrderId);

    // Determine total unique item IDs to validate coverage
    let allItemIds = [];
    if (window._currentPhases && window._currentPhases.length > 0) {
        // Only validate coverage for the currently active phase
        const phase = window._currentPhases[window._activePhaseIndex || 0];
        (phase.items || []).forEach(item => {
            if (!allItemIds.includes(item.id)) allItemIds.push(item.id);
        });
    } else {
        allItemIds = (order && order.order_items) ? order.order_items.map(i => i.id) : [];
    }
    const totalItems = allItemIds.length;

    // Ensure every item has at least one employee
    const coveredItems = new Set(itemAssignments.map(ia => ia.order_item_id));
    if (coveredItems.size < totalItems) {
        const msg = document.getElementById('assignValidationMsg');
        msg && msg.classList.remove('hidden');
        return;
    }

    const priority = (order && order.priority) ? order.priority.toLowerCase() : 'normal';
    const notes    = document.getElementById('assignmentNotes').value;

    const assignBtn = document.getElementById('assignBtn');
    assignBtn.disabled = true;
    assignBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Assigning...';

    fetch('/assignments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
        body: JSON.stringify({
            order_id: selectedOrderId,
            priority,
            notes: notes || null,
            item_assignments: itemAssignments,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeAssignmentModal();
            showToast(`Order ${selectedOrderId} assigned successfully!`, 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast(data.message || 'Failed to assign. Please try again.', 'error');
            assignBtn.disabled = false;
            assignBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.82m5.84-2.56a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.82m2.56-5.84a14.98 14.98 0 00-12.12 6.16"/></svg> Assign Items';
        }
    })
    .catch(() => {
        showToast('An error occurred. Please try again.', 'error');
        assignBtn.disabled = false;
        assignBtn.innerHTML = 'Assign Items';
    });
}

window.quickAssignOrder = function(orderId) {
    currentEmployeeName = '';
    prefillEmployeeId = null;
    document.getElementById('modalSubtitle').textContent = 'Assign each product to an employee — different workers can handle different items.';

    const orderSelect = document.getElementById('orderSelect');
    orderSelect.innerHTML = '<option value="">-- Select an order --</option>' +
        availableOrders.map(o =>
            `<option value="${o.order_id}">${o.order_id} — ${o.customer}</option>`
        ).join('');

    document.getElementById('orderPreview').classList.add('hidden');
    document.getElementById('assignBtn').disabled = true;
    document.getElementById('assignmentModal').classList.remove('hidden');

    if (orderId) {
        orderSelect.value = orderId;
        updateOrderPreview();
    }
}

// ========== View Full Details Modal ==========
window.viewAssignmentDetails = function(order) {
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

window.closeAssignmentDetailsModal = function() {
    document.getElementById('assignmentDetailsModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentDetailAssignment = null;
}

// ========== Update Status Modal ==========
window.openUpdateStatusModal = function(order) {
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

window.closeUpdateStatusModal = function() {
    document.getElementById('updateStatusModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentUpdateAssignment = null;
    selectedNewStatus = '';
}

window.selectStatus = function(status) {
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

window.saveStatusUpdate = function() {
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

// ========== Toast ==========
window.showToast = function(message, type) {
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
