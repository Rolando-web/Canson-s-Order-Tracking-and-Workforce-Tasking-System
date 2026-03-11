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
                'Completed':   'bg-green-50 text-green-600 border-green-200',
                'In-Progress': 'bg-blue-50 text-blue-600 border-blue-200',
                'Pending':     'bg-gray-100 text-gray-500 border-gray-200',
                'Delivered':   'bg-emerald-50 text-emerald-600 border-emerald-200',
            }[phase.status] || 'bg-gray-100 text-gray-500 border-gray-200';
            const delivDate = new Date(phase.delivery_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

            // Phase-level totals
            let phaseTotalReq = 0, phaseTotalDone = 0;
            phase.items.forEach(function(item) { phaseTotalReq += item.required_qty; phaseTotalDone += item.completed_qty; });
            const phasePct = phaseTotalReq > 0 ? Math.round((phaseTotalDone / phaseTotalReq) * 100) : 0;
            const phaseBarColor = phasePct >= 100 ? 'bg-emerald-500' : phasePct > 0 ? 'bg-blue-400' : 'bg-gray-200';

            const rows = phase.items.map(function(item) {
                const pct = item.required_qty > 0 ? Math.round((item.completed_qty / item.required_qty) * 100) : 0;
                const barColor = pct >= 100 ? 'bg-emerald-500' : pct > 0 ? 'bg-blue-400' : 'bg-gray-200';
                return `<tr class="border-t border-gray-100">
                    <td class="px-3 py-2 text-sm text-gray-800 whitespace-nowrap">${item.name}</td>
                    <td class="px-3 py-2 text-sm text-center font-medium text-gray-700 w-20">${item.required_qty}</td>
                    <td class="px-3 py-2 text-sm text-center font-medium text-gray-700 w-20">${item.completed_qty}</td>
                    <td class="px-3 py-2 w-36">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="${barColor} h-full rounded-full transition-all" style="width:${pct}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-gray-500 w-8 text-right">${pct}%</span>
                        </div>
                    </td>
                </tr>`;
            }).join('');

            html += `<div class="mb-4 rounded-lg overflow-hidden border border-indigo-200">
                <div class="flex items-center justify-between px-4 py-2.5 bg-indigo-50">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-indigo-700 uppercase">Phase ${phase.number}</span>
                        <div class="flex items-center gap-1.5 w-24">
                            <div class="flex-1 h-1.5 bg-indigo-100 rounded-full overflow-hidden">
                                <div class="${phaseBarColor} h-full rounded-full" style="width:${phasePct}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-indigo-600">${phasePct}%</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="loadPhaseNotes(${phase.phase_id}, ${phase.number})" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold underline">Notes</button>
                        <span class="text-[10px] text-gray-500">${delivDate}</span>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border ${statusBadge}">${phase.status}</span>
                    </div>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase">Item</th>
                            <th class="px-3 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase w-20">Required</th>
                            <th class="px-3 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase w-20">Done</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase w-36">Progress</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
        });
        document.getElementById('opPhasedContent').innerHTML = html;
        // Auto-load notes for the first phase
        if (order.phases_progress.length > 0 && order.phases_progress[0].phase_id) {
            loadPhaseNotes(order.phases_progress[0].phase_id, order.phases_progress[0].number);
        }
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
    document.getElementById('opTrackingNotesSection')?.classList.add('hidden');
}

// ========== Phase Tracking Notes ==========
window._currentTrackingPhaseId = null;

window.loadPhaseNotes = function(phaseId, phaseNumber) {
    window._currentTrackingPhaseId = phaseId;
    const section = document.getElementById('opTrackingNotesSection');
    const label = document.getElementById('opNotesPhaseLabel');
    const textarea = document.getElementById('opPhaseNotesTextarea');

    section.classList.remove('hidden');
    label.textContent = 'Phase ' + phaseNumber;
    textarea.value = '';
    textarea.placeholder = 'Loading...';

    fetch('/phases/' + phaseId + '/notes', {
        headers: { 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        textarea.value = data.notes || '';
        textarea.placeholder = 'Add tracking notes for this phase...';
    })
    .catch(() => {
        textarea.placeholder = 'Failed to load notes';
    });
}

window.savePhaseNotes = function() {
    if (!window._currentTrackingPhaseId) return;
    const textarea = document.getElementById('opPhaseNotesTextarea');
    const btn = document.getElementById('opSaveNotesBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    fetch('/phases/' + window._currentTrackingPhaseId + '/notes', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
        body: JSON.stringify({ notes: textarea.value }),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            btn.textContent = 'Saved!';
            btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            btn.classList.add('bg-green-600');
            setTimeout(() => {
                btn.textContent = 'Save Notes';
                btn.classList.remove('bg-green-600');
                btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            }, 1500);
        } else {
            btn.textContent = 'Save Notes';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Save Notes';
    });
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

            // Phase label
            const phaseLabel = order.phase_number
                ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-bold rounded-full">Phase ${order.phase_number}</span>`
                : '';

            // Items list
            const itemsHtml = (order.items_list || []).map(item =>
                `<span class="text-xs text-gray-700">${item.quantity}x ${item.name}</span>`
            ).join('<span class="text-gray-300 mx-1">|</span>');

            return `
                <div class="border border-gray-200 rounded-xl p-4 bg-white hover:shadow-md transition-all">
                    <div class="flex items-start justify-between mb-3 pb-2 border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-sm font-bold text-gray-900">${order.order_id}</h4>
                                ${phaseLabel}
                            </div>
                            <p class="text-xs text-gray-500">${order.customer}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border ${priorityColors[order.priority] || priorityColors['normal']}">${order.priority.toUpperCase()}</span>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border ${statusColors[order.status]}">${order.status.replace('_', ' ').toUpperCase()}</span>
                        </div>
                    </div>

                    <div class="space-y-2 mb-3">
                        <div class="flex flex-wrap items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3"/></svg>
                            ${itemsHtml || '<span class="text-xs text-gray-400">No items</span>'}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                            Deliver by ${new Date(order.delivery_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <p class="text-xs text-gray-500">Assigned: <span class="font-medium text-gray-700">${new Date(order.assigned_date).toLocaleDateString()}</span></p>
                        <button onclick="openOrderProgressModal('${order.order_id}')" class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                            View Progress
                        </button>
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
        // --- Phased order: all phases assigned to same employee(s) ---
        phaseTabsWrapper.classList.remove('hidden');
        window._currentPhases = phases;
        window._activePhaseIndex = 0;

        // Use Phase 1 items for employee assignment (backend replicates to all phases)
        const firstPhase = phases[0];
        (firstPhase.items || []).forEach(item => {
            window.selectedEmployeesPerItem[item.id] = [];
            if (prefillEmployeeId) {
                const emp = workersData.find(w => w.id === prefillEmployeeId);
                if (emp) window.selectedEmployeesPerItem[item.id] = [{ id: emp.id, name: emp.name }];
            }
        });

        // Show read-only phase summary badges (not selectable, all assigned together)
        const allPhases = order.all_phases_status || [];
        phaseTabs.innerHTML =
            '<div class="flex items-center gap-2 flex-wrap">' +
            '<span class="text-xs font-medium text-gray-500 mr-1">All phases assigned together:</span>' +
            allPhases.map(phaseInfo => {
                const date = new Date(phaseInfo.delivery_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                return `<span class="px-3 py-1.5 rounded-lg text-xs font-semibold border bg-indigo-50 text-indigo-600 border-indigo-200">
                    Phase ${phaseInfo.number} <span class="opacity-70 ml-1">${date}</span>
                </span>`;
            }).join('') +
            '</div>';

        // Show Phase 1 items with total qty across all phases
        const rowsHtml = (firstPhase.items || []).map(item => {
            // Sum qty across all phases for this item
            let totalQty = 0;
            phases.forEach(p => {
                const pi = (p.items || []).find(i => i.name === item.name);
                if (pi) totalQty += pi.required_qty;
            });

            return `
            <tr class="item-assign-row" data-item-id="${item.id}">
                <td class="px-4 py-3 align-top">
                    <p class="text-sm font-semibold text-gray-900">${item.name}</p>
                    <p class="text-xs text-gray-400">\u20B1${item.price.toLocaleString('en-US', {minimumFractionDigits: 2})} / unit</p>
                </td>
                <td class="px-4 py-3 text-center align-top">
                    <span class="text-sm font-bold text-gray-900">${totalQty}</span>
                    <p class="text-[10px] text-gray-400">${phases.length} phases</p>
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
        (firstPhase.items || []).forEach(item => renderItemTags(item.id));
        checkAllAssigned();

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
        // Check items from Phase 1 (all phases get same employees)
        const firstPhase = window._currentPhases[0];
        (firstPhase.items || []).forEach(item => {
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

    // Build flat item_assignments array (Phase 1 items + employees; backend replicates to all phases)
    const itemAssignments = [];
    if (window._currentPhases && window._currentPhases.length > 0) {
        // Phased order: send Phase 1 item-employee mapping (backend auto-assigns all phases)
        const firstPhase = window._currentPhases[0];
        (firstPhase.items || []).forEach(item => {
            (window.selectedEmployeesPerItem[item.id] || []).forEach(emp => {
                itemAssignments.push({
                    phase_item_id: item.id,
                    phase_id:      firstPhase.phase_id,
                    employee_id:   emp.id,
                });
            });
        });
    } else {
        document.querySelectorAll('.item-assign-row').forEach(row => {
            const itemId = parseInt(row.dataset.itemId);
            (window.selectedEmployeesPerItem[itemId] || []).forEach(emp => {
                itemAssignments.push({
                    phase_item_id: itemId,
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
    const coveredItems = new Set(itemAssignments.map(ia => ia.phase_item_id));
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
