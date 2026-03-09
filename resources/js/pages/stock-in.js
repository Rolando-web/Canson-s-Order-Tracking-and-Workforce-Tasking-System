document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const tabs = document.querySelectorAll('.stockin-tab-btn');
    const formTab = document.getElementById('tab-stock-in-form');
    const historyTab = document.getElementById('tab-stock-in-history');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => { t.className = 'stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50'; });
            tab.className = 'stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white';

            if (tab.dataset.tab === 'stock-in-form') {
                formTab.classList.remove('hidden');
                historyTab.classList.add('hidden');
            } else {
                formTab.classList.add('hidden');
                historyTab.classList.remove('hidden');
            }
        });
    });

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
    const year = new Date().getFullYear();
    const rand = String(Math.floor(1000 + Math.random() * 9000));
    return `${prefix}-${year}-${rand}`;
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
            item_id:  _stockInItemId,
            quantity: qty,
            supplier: document.getElementById('stockInSupplier').value,
            notes:    document.getElementById('stockInNotes').value,
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
