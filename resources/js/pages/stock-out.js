// Filter history
window.filterStockOutHistory = function() {
    const search = document.getElementById('stockOutHistorySearch')?.value.toLowerCase() ?? '';
    document.querySelectorAll('.stockout-history-row').forEach(row => {
        const matchRef    = row.dataset.ref?.includes(search) ?? false;
        const matchReason = row.dataset.reason?.includes(search) ?? false;
        row.style.display = !search || matchRef || matchReason ? '' : 'none';
    });
};

// ========== Batch Detail Modal ==========

window.openStockOutDetailModal = function(batch) {
    document.getElementById('soDetailModalRef').textContent       = batch.reference;
    document.getElementById('soDetailModalDate').textContent      = batch.date;
    document.getElementById('soDetailModalReason').textContent    = batch.reason;
    document.getElementById('soDetailModalCreatedBy').textContent = batch.created_by;
    document.getElementById('soDetailModalNotes').textContent     = batch.notes;

    // Order & Phase Traceability
    const orderSection = document.getElementById('soDetailOrderSection');
    if (batch.order_number) {
        document.getElementById('soDetailOrderNumber').textContent = 'Order #' + batch.order_number;
        document.getElementById('soDetailCustomerName').textContent = batch.customer_name || '—';

        // Order status badge
        const statusEl = document.getElementById('soDetailOrderStatus');
        const statusColors = {
            'Pending':            'bg-amber-100 text-amber-700',
            'In-Progress':        'bg-blue-100 text-blue-700',
            'Ready for Delivery': 'bg-purple-100 text-purple-700',
            'Delivered':          'bg-emerald-100 text-emerald-700',
            'Cancelled':          'bg-red-100 text-red-700',
        };
        const statusColor = statusColors[batch.order_status] || 'bg-gray-100 text-gray-600';
        statusEl.className = `inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${statusColor}`;
        statusEl.textContent = batch.order_status || '—';

        // Phase breakdown
        const phasesContainer = document.getElementById('soDetailPhasesContainer');
        const phasesList = document.getElementById('soDetailPhasesList');

        if (batch.phases && batch.phases.length > 0) {
            let phasesHtml = '';
            for (const phase of batch.phases) {
                const isComplete = phase.status === 'Completed' || phase.status === 'Delivered';
                const phBg = isComplete ? 'bg-emerald-100 border-emerald-200' : 'bg-white border-gray-200';
                const phText = isComplete ? 'text-emerald-700' : 'text-gray-700';
                const phStatusBg = isComplete ? 'bg-emerald-500' : (phase.status === 'In-Progress' ? 'bg-blue-500' : 'bg-gray-400');

                phasesHtml += `
                <div class="flex items-center gap-2 px-3 py-2 rounded-lg border ${phBg}">
                    <div class="w-1.5 h-1.5 rounded-full ${phStatusBg} shrink-0"></div>
                    <div>
                        <p class="text-xs font-bold ${phText}">Phase ${phase.number}</p>
                        <p class="text-[10px] text-gray-400">${phase.status} &bull; ${phase.delivery}</p>
                    </div>
                </div>`;
            }
            phasesList.innerHTML = phasesHtml;
            phasesContainer.classList.remove('hidden');
        } else {
            phasesContainer.classList.add('hidden');
            phasesList.innerHTML = '';
        }

        orderSection.classList.remove('hidden');
    } else {
        orderSection.classList.add('hidden');
    }

    // Items table
    const tbody = document.getElementById('soDetailModalItemsBody');
    let html = '';
    for (const item of batch.items) {
        html += `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-semibold text-gray-900">${item.name}</td>
            <td class="px-4 py-3 text-xs font-mono text-gray-500">${item.code}</td>
            <td class="px-4 py-3 text-sm text-gray-500 text-right">${Number(item.prev).toLocaleString()}</td>
            <td class="px-4 py-3 text-sm text-right">
                <span class="font-bold text-red-600">-${Number(item.qty).toLocaleString()}</span>
            </td>
            <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                ${Number(item.new).toLocaleString()} <span class="text-xs font-normal text-gray-400">${item.unit}</span>
            </td>
        </tr>`;
    }
    tbody.innerHTML = html;

    document.getElementById('stockOutDetailModal').classList.remove('hidden');
};

window.closeStockOutDetailModal = function() {
    document.getElementById('stockOutDetailModal').classList.add('hidden');
};
