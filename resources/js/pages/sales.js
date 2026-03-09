window.openSaleModal = function(sale) {
    document.getElementById('modalOrderId').textContent  = sale.id;
    document.getElementById('modalOrderDate').textContent = sale.date;
    document.getElementById('modalInitial').textContent  = sale.customer.charAt(0).toUpperCase();
    document.getElementById('modalCustomer').textContent = sale.customer;
    document.getElementById('modalContact').textContent  = sale.contact || '—';
    document.getElementById('modalAmount').textContent   = '₱' + Number(sale.amount).toLocaleString();
    document.getElementById('modalTotal').textContent    = '₱' + Number(sale.amount).toLocaleString();
    document.getElementById('modalPriority').textContent = sale.priority || 'Normal';

    const badge = document.getElementById('modalStatusBadge');
    badge.textContent = sale.status;
    badge.className = 'ml-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold ' + (sale.statusBadge || 'bg-gray-100 text-gray-600');

    const addrDiv = document.getElementById('modalAddressDiv');
    if (sale.address) { document.getElementById('modalAddress').textContent = sale.address; addrDiv.classList.remove('hidden'); }
    else { addrDiv.classList.add('hidden'); }

    const notesDiv = document.getElementById('modalNotesDiv');
    if (sale.notes) { document.getElementById('modalNotes').textContent = sale.notes; notesDiv.classList.remove('hidden'); }
    else { notesDiv.classList.add('hidden'); }

    const itemsEl = document.getElementById('modalItems');
    if (sale.order_items && sale.order_items.length > 0) {
        itemsEl.innerHTML = sale.order_items.map(item => `
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">${item.name}</p>
                    <p class="text-xs text-gray-400">${item.qty} units × ₱${Number(item.price).toLocaleString()}</p>
                </div>
                <p class="text-sm font-semibold text-gray-900">₱${Number(item.subtotal).toLocaleString()}</p>
            </div>`).join('');
    } else {
        itemsEl.innerHTML = '<p class="text-sm text-gray-400">No items found.</p>';
    }

    document.getElementById('saleDetailModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

window.closeSaleModal = function() {
    document.getElementById('saleDetailModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('saleDetailModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeSaleModal();
        });
    }
});
