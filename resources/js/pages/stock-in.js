document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const tabs = document.querySelectorAll('.stockin-tab-btn');
    const tabPanels = {
        'stock-in-form':    document.getElementById('tab-stock-in-form'),
        'stock-in-history': document.getElementById('tab-stock-in-history'),
        'suppliers':        document.getElementById('tab-suppliers'),
    };

    const headerTitle    = document.getElementById('stockin-page-title');
    const headerSubtitle = document.getElementById('stockin-page-subtitle');

    const headerMap = {
        'stock-in-form':    { title: 'Stock In',          subtitle: 'Add incoming stock to inventory' },
        'stock-in-history': { title: 'Movement History',  subtitle: 'Track all stock movements and transactions' },
        'suppliers':        { title: 'Suppliers',          subtitle: 'Manage your suppliers and vendor information' },
    };

    function activateTab(tabKey) {
        tabs.forEach(t => { t.className = 'stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50'; });
        Object.values(tabPanels).forEach(p => { if (p) p.classList.add('hidden'); });

        const btn = [...tabs].find(t => t.dataset.tab === tabKey);
        const panel = tabPanels[tabKey];
        if (btn) btn.className = 'stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white';
        if (panel) panel.classList.remove('hidden');

        const header = headerMap[tabKey];
        if (header) {
            if (headerTitle)    headerTitle.textContent    = header.title;
            if (headerSubtitle) headerSubtitle.textContent = header.subtitle;
        }

        localStorage.setItem('stockin-active-tab', tabKey);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => activateTab(tab.dataset.tab));
    });

    // Restore last active tab on page load
    const saved = localStorage.getItem('stockin-active-tab');
    if (saved && tabPanels[saved]) {
        activateTab(saved);
    }

    // Initialize batch form defaults
    document.getElementById('batchDate').value = new Date().toISOString().split('T')[0];
});

// Filter product list
window.filterStockInItems = function() {
    const search = document.getElementById('stockInSearch')?.value.toLowerCase() ?? '';
    document.querySelectorAll('.stockin-item-row').forEach(row => {
        row.style.display = !search || row.dataset.name.includes(search) ? '' : 'none';
    });
};

// Filter history
window.filterStockInHistory = function() {
    const search = document.getElementById('stockInHistorySearch')?.value.toLowerCase() ?? '';
    document.querySelectorAll('.stockin-history-row').forEach(row => {
        const matchRef      = row.dataset.ref?.includes(search) ?? false;
        const matchSupplier = row.dataset.supplier?.includes(search) ?? false;
        row.style.display = !search || matchRef || matchSupplier ? '' : 'none';
    });
};

// ========== Batch Detail Modal ==========

window.openStockInDetailModal = function(batch) {
    document.getElementById('detailModalRef').textContent        = batch.reference;
    document.getElementById('detailModalDate').textContent       = batch.date;
    document.getElementById('detailModalSupplier').textContent   = batch.supplier;
    document.getElementById('detailModalCreatedBy').textContent  = batch.created_by;
    document.getElementById('detailModalNotes').textContent      = batch.notes;

    const tbody = document.getElementById('detailModalItemsBody');
    let html = '';
    let totalCost = 0;
    for (const item of batch.items) {
        const lineCost = Number(item.unit_cost || 0) * Number(item.qty);
        totalCost += lineCost;
        html += `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-semibold text-gray-900">${item.name}</td>
            <td class="px-4 py-3 text-xs font-mono text-gray-500">${item.code}</td>
            <td class="px-4 py-3 text-sm text-right text-gray-600">${Number(item.unit_cost || 0) > 0 ? '₱' + Number(item.unit_cost).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '—'}</td>
            <td class="px-4 py-3 text-sm text-gray-500 text-right">${Number(item.prev).toLocaleString()}</td>
            <td class="px-4 py-3 text-sm text-right">
                <span class="font-bold text-green-600">+${Number(item.qty).toLocaleString()}</span>
            </td>
            <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                ${Number(item.new).toLocaleString()} <span class="text-xs font-normal text-gray-400">${item.unit}</span>
            </td>
        </tr>`;
    }
    if (totalCost > 0) {
        html += `
        <tr class="bg-gray-50 border-t border-gray-200">
            <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-700 text-right">Total Cost:</td>
            <td class="px-4 py-3 text-sm font-bold text-emerald-700 text-right">₱${totalCost.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
            <td colspan="3"></td>
        </tr>`;
    }
    tbody.innerHTML = html;

    document.getElementById('stockInDetailModal').classList.remove('hidden');
};

window.closeStockInDetailModal = function() {
    document.getElementById('stockInDetailModal').classList.add('hidden');
};

// ========== Batch Stock In ==========

// batchItems: { [productId]: { id, name, code, stock, unit, qty } }
let batchItems = {};

window.addToBatch = function(item) {
    if (batchItems[item.id]) {
        batchItems[item.id].qty += 1;
    } else {
        batchItems[item.id] = { ...item, qty: 1, unit_cost: 0 };
    }
    renderBatch();
    updateAddBtn(item.id, true);
};

window.removeBatchItem = function(itemId) {
    delete batchItems[itemId];
    renderBatch();
    updateAddBtn(itemId, false);
};

window.changeBatchQty = function(itemId, delta) {
    if (!batchItems[itemId]) return;
    batchItems[itemId].qty = Math.max(1, batchItems[itemId].qty + delta);
    renderBatch();
};

window.updateBatchQty = function(itemId, val) {
    if (!batchItems[itemId]) return;
    batchItems[itemId].qty = Math.max(1, parseInt(val) || 1);
};

window.updateBatchCost = function(itemId, val) {
    if (!batchItems[itemId]) return;
    batchItems[itemId].unit_cost = Math.max(0, parseFloat(val) || 0);
};

window.clearBatch = function() {
    const ids = Object.keys(batchItems);
    batchItems = {};
    ids.forEach(id => updateAddBtn(id, false));
    renderBatch();
};

function updateAddBtn(itemId, inBatch) {
    const btn = document.getElementById(`add-btn-${itemId}`);
    if (!btn) return;
    if (inBatch) {
        btn.className = 'inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-600 text-white border border-emerald-600 hover:bg-emerald-700 transition-colors';
        btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Added`;
    } else {
        btn.className = 'inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 transition-colors';
        btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg> Add`;
    }
}

function renderBatch() {
    const list = document.getElementById('batchItemsList');
    const count = Object.keys(batchItems).length;

    document.getElementById('batchCount').textContent = count + (count === 1 ? ' item' : ' items');
    document.getElementById('batchSubmitText').textContent = count > 0
        ? `Submit Stock In (${count} item${count !== 1 ? 's' : ''})`
        : 'Submit Stock In';

    if (count === 0) {
        list.innerHTML = `<div id="batchEmptyState" class="text-center py-8 border-2 border-dashed border-gray-200 rounded-xl">
            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
            <p class="text-sm text-gray-400">No items added yet</p>
            <p class="text-xs text-gray-300 mt-1">Click <strong class="text-gray-400">+ Add</strong> from the product list</p>
        </div>`;
        return;
    }

    let html = '';
    for (const [id, item] of Object.entries(batchItems)) {
        const newStock = item.stock + item.qty;
        html += `
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">${item.name}</p>
                <p class="text-xs text-gray-400 mt-0.5">${item.code} &middot; Stock: <span class="text-gray-500">${item.stock.toLocaleString()}</span> &rarr; <span class="font-bold text-green-600">${newStock.toLocaleString()}</span> <span class="text-gray-400">${item.unit}</span></p>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <div class="flex flex-col items-center">
                    <span class="text-[0.6rem] text-gray-400 mb-0.5">Cost</span>
                    <input type="number" min="0" step="0.01" value="${item.unit_cost || ''}" placeholder="0.00"
                        onchange="updateBatchCost(${id}, this.value)"
                        class="w-16 text-center border border-gray-300 rounded-md text-xs py-1 focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-[0.6rem] text-gray-400 mb-0.5">Qty</span>
                    <div class="flex items-center gap-0.5">
                        <button onclick="changeBatchQty(${id}, -1)" class="w-6 h-6 flex items-center justify-center border border-gray-300 rounded-md text-gray-500 hover:bg-gray-100 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6"/></svg>
                        </button>
                        <input type="number" min="1" value="${item.qty}"
                            onchange="updateBatchQty(${id}, this.value); renderBatch();"
                            class="w-12 text-center border border-gray-300 rounded-md text-xs font-bold py-1 focus:outline-none focus:ring-1 focus:ring-green-500">
                        <button onclick="changeBatchQty(${id}, 1)" class="w-6 h-6 flex items-center justify-center border border-gray-300 rounded-md text-gray-500 hover:bg-gray-100 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <button onclick="removeBatchItem(${id})" class="w-7 h-7 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>`;
    }
    list.innerHTML = html;
}

window.submitBatchStockIn = function() {
    const count = Object.keys(batchItems).length;
    if (count === 0) {
        showToast('Please add at least one item to stock in.', 'red');
        return;
    }

    const date = document.getElementById('batchDate').value;
    if (!date) {
        showToast('Please select a date received.', 'red');
        return;
    }

    const supplier = document.getElementById('batchSupplier').value;
    if (!supplier) {
        showToast('Please select a supplier before submitting.', 'red');
        return;
    }

    const items = Object.values(batchItems).map(item => ({
        item_id:   item.id,
        quantity:  item.qty,
        unit_cost: item.unit_cost || 0,
    }));

    const btn = document.getElementById('batchSubmitBtn');
    btn.disabled = true;
    document.getElementById('batchSubmitText').textContent = 'Processing...';

    fetch('/inventory/stock-in/bulk', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            items,
            supplier_id: supplier,
            notes:       document.getElementById('batchNotes').value,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(`Stock In recorded for ${data.count} item${data.count !== 1 ? 's' : ''}! Ref: ${data.reference}`, 'green');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(data.message || 'Failed to record stock in.', 'red');
            btn.disabled = false;
            document.getElementById('batchSubmitText').textContent = `Submit Stock In (${count} item${count !== 1 ? 's' : ''})`;
        }
    })
    .catch(() => {
        showToast('An error occurred. Please try again.', 'red');
        btn.disabled = false;
        document.getElementById('batchSubmitText').textContent = `Submit Stock In (${count} item${count !== 1 ? 's' : ''})`;
    });
};

function showToast(message, color = 'green') {
    const bg = color === 'red' ? 'bg-red-600' : 'bg-green-600';
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-[60] ${bg} text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2 text-sm font-medium max-w-sm`;
    toast.innerHTML = `<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// ========== Supplier CRUD ==========

window.filterSuppliers = function() {
    const search = document.getElementById('supplierSearch')?.value.toLowerCase() ?? '';
    document.querySelectorAll('.supplier-row').forEach(row => {
        row.style.display = !search || row.dataset.name.includes(search) ? '' : 'none';
    });
};

window.openAddSupplierModal = function() {
    document.getElementById('supplierEditId').value = '';
    document.getElementById('supplierName').value = '';
    document.getElementById('supplierAddress').value = '';
    document.getElementById('supplierEmail').value = '';
    document.getElementById('supplierPhone').value = '';
    document.getElementById('supplierModalTitle').textContent = 'Add Supplier';
    document.getElementById('supplierSubmitText').textContent = 'Save Supplier';
    document.getElementById('supplierModal').classList.remove('hidden');
};

window.openEditSupplierModal = function(supplier) {
    document.getElementById('supplierEditId').value = supplier.id;
    document.getElementById('supplierName').value = supplier.name;
    document.getElementById('supplierAddress').value = supplier.address;
    document.getElementById('supplierEmail').value = supplier.email;
    document.getElementById('supplierPhone').value = supplier.phone;
    document.getElementById('supplierModalTitle').textContent = 'Edit Supplier';
    document.getElementById('supplierSubmitText').textContent = 'Update Supplier';
    document.getElementById('supplierModal').classList.remove('hidden');
};

window.closeSupplierModal = function() {
    document.getElementById('supplierModal').classList.add('hidden');
};

window.submitSupplier = function() {
    const id      = document.getElementById('supplierEditId').value;
    const name    = document.getElementById('supplierName').value.trim();
    const address = document.getElementById('supplierAddress').value.trim();
    const email   = document.getElementById('supplierEmail').value.trim();
    const phone   = document.getElementById('supplierPhone').value.trim();

    if (!name || !address || !email || !phone) {
        alert('Please fill in all required fields.');
        return;
    }

    const url    = id ? `/suppliers/${id}` : '/suppliers';
    const method = id ? 'PUT' : 'POST';

    const btn = document.getElementById('supplierSubmitBtn');
    if (btn) { btn.disabled = true; }

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ name, address, email, phone }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeSupplierModal();
            showToast(id ? 'Supplier updated successfully!' : 'Supplier added successfully!');
            setTimeout(() => location.reload(), 800);
        } else {
            alert('Failed to save supplier.');
            if (btn) btn.disabled = false;
        }
    })
    .catch(() => {
        alert('An error occurred. Please try again.');
        if (btn) btn.disabled = false;
    });
};

window.deleteSupplier = function(id, name) {
    if (!confirm(`Are you sure you want to remove "${name}" from your suppliers?`)) return;

    fetch(`/suppliers/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Supplier removed successfully!');
            setTimeout(() => location.reload(), 800);
        } else {
            alert('Failed to remove supplier.');
        }
    })
    .catch(() => alert('An error occurred. Please try again.'));
};

// ========== Supplier Toggle ==========
window.toggleSupplierView = function(view) {
    const activeTable = document.getElementById('activeSupplierTable');
    const archivedTable = document.getElementById('archivedSupplierTable');
    const activeBtn = document.getElementById('activeSupplierBtn');
    const archivedBtn = document.getElementById('archivedSupplierBtn');
    const addBtn = document.getElementById('addSupplierBtn');

    if (view === 'active') {
        // Show active table, hide archived
        activeTable.classList.remove('hidden');
        archivedTable.classList.add('hidden');

        // Update button states - active button gets emerald background
        activeBtn.classList.add('bg-emerald-600', 'text-white');
        activeBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
        archivedBtn.classList.remove('bg-emerald-600', 'text-white');
        archivedBtn.classList.add('text-gray-600', 'hover:text-gray-900');

        // Show add button for active suppliers
        addBtn.classList.remove('hidden');
    } else {
        // Show archived table, hide active
        activeTable.classList.add('hidden');
        archivedTable.classList.remove('hidden');

        // Update button states - archived button gets emerald background
        archivedBtn.classList.add('bg-emerald-600', 'text-white');
        archivedBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
        activeBtn.classList.remove('bg-emerald-600', 'text-white');
        activeBtn.classList.add('text-gray-600', 'hover:text-gray-900');

        // Hide add button for archived view
        addBtn.classList.add('hidden');
    }

    // Clear search when switching views
    const searchInput = document.getElementById('supplierSearch');
    if (searchInput) {
        searchInput.value = '';
        filterSuppliers(); // Reset filter
    }
};
