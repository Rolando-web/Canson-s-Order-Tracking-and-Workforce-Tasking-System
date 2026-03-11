document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const tabs = document.querySelectorAll('.stockin-tab-btn');
    const tabPanels = {
        'stock-in-form': document.getElementById('tab-stock-in-form'),
        'stock-in-history': document.getElementById('tab-stock-in-history'),
        'suppliers': document.getElementById('tab-suppliers'),
    };

    function activateTab(tabKey) {
        tabs.forEach(t => { t.className = 'stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50'; });
        Object.values(tabPanels).forEach(p => { if (p) p.classList.add('hidden'); });

        const btn = [...tabs].find(t => t.dataset.tab === tabKey);
        const panel = tabPanels[tabKey];
        if (btn) btn.className = 'stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white';
        if (panel) panel.classList.remove('hidden');

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

    document.getElementById('stockInQty')?.addEventListener('input', updateStockInPreview);
});

// Filter items
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
        row.style.display = !search || row.dataset.name.includes(search) ? '' : 'none';
    });
};

// ========== Stock In Modal ==========
function generateRef(prefix) {
    const chars = '0123456789ABCDEF';
    let code = '';
    for (let i = 0; i < 6; i++) code += chars[Math.floor(Math.random() * chars.length)];
    return `${prefix}-${code}`;
}

let _stockInCurrent = 0;
let _stockInItemId = null;

window.openStockInModal = function (item) {
    _stockInCurrent = item.stock ?? 0;
    _stockInItemId = item.id;

    document.getElementById('stockInItemName').textContent    = item.name ?? '—';
    document.getElementById('stockInItemCode').textContent    = 'Code: ' + (item.code ?? 'N/A');
    document.getElementById('stockInCurrentStock').textContent = item.stock ?? '—';
    document.getElementById('stockInUnit').textContent        = item.unit ?? '';

    const imgBox = document.getElementById('stockInItemImage');
    if (item.image) {
        imgBox.innerHTML = `<img src="${item.image}" class="w-full h-full object-cover" alt="${item.name}">`;
    } else {
        imgBox.innerHTML = `<svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75z"/></svg>`;
    }

    document.getElementById('stockInQty').value       = 1;
    document.getElementById('stockInSupplier').value  = '';
    document.getElementById('stockInReference').value = generateRef('SI');
    document.getElementById('stockInNotes').value     = '';
    document.getElementById('stockInDate').value      = new Date().toISOString().split('T')[0];

    updateStockInPreview();
    document.getElementById('stockInModal').classList.remove('hidden');
};

window.closeStockInModal = function () {
    document.getElementById('stockInModal').classList.add('hidden');
};

window.adjustStockInQty = function (delta) {
    const input = document.getElementById('stockInQty');
    const val = Math.max(1, (parseInt(input.value) || 1) + delta);
    input.value = val;
    updateStockInPreview();
};

function updateStockInPreview() {
    const qty = parseInt(document.getElementById('stockInQty')?.value) || 0;
    const newVal = _stockInCurrent + qty;
    document.getElementById('stockInPreviewOld').textContent  = _stockInCurrent.toLocaleString();
    document.getElementById('stockInPreviewNew').textContent  = newVal.toLocaleString();
    document.getElementById('stockInPreviewDiff').textContent = `(+${qty.toLocaleString()})`;
}

window.submitStockIn = function () {
    const qty  = parseInt(document.getElementById('stockInQty').value) || 0;
    const date = document.getElementById('stockInDate').value;
    if (qty < 1) { alert('Please enter a valid quantity.'); return; }
    if (!date)   { alert('Please select a date received.'); return; }

    const btn = document.querySelector('#stockInModal button[onclick="submitStockIn()"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Processing...'; }

    fetch('/inventory/stock-in', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            item_id:     _stockInItemId,
            quantity:    qty,
            supplier_id: document.getElementById('stockInSupplier').value || null,
            notes:       document.getElementById('stockInNotes').value,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeStockInModal();
            showToast('Stock In recorded successfully!', 'green');
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Failed to record stock in.');
            if (btn) { btn.disabled = false; btn.textContent = 'Confirm Stock In'; }
        }
    })
    .catch(() => {
        alert('An error occurred. Please try again.');
        if (btn) { btn.disabled = false; btn.textContent = 'Confirm Stock In'; }
    });
};

function showToast(message, color = 'green') {
    const bg = color === 'red' ? 'bg-red-600' : 'bg-green-600';
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-[60] ${bg} text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2 text-sm font-medium`;
    toast.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s ease'; setTimeout(() => toast.remove(), 300); }, 3000);
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
