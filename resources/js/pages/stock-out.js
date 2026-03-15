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
