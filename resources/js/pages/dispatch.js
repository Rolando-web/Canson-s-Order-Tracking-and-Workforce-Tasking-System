// ========== Order Detail Modal (show/hide only — content rendered by Blade) ==========
window.openDetailModal = function(dispatchKey) {
    var modal = document.getElementById('dispatchDetailModal-' + dispatchKey);
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

window.closeDetailModal = function(dispatchKey) {
    var modal = document.getElementById('dispatchDetailModal-' + dispatchKey);
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// ========== Print Invoice ==========
window.printInvoice = function(dispatchKey) {
    var invoiceArea = document.getElementById('invoiceArea-' + dispatchKey);
    if (!invoiceArea) return;

    var printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write('<!DOCTYPE html><html><head><title>Invoice</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
    printWindow.document.write('body { font-family: "Segoe UI", Arial, sans-serif; color: #1f2937; padding: 40px; font-size: 13px; }');
    printWindow.document.write('.header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #059669; }');
    printWindow.document.write('.header h1 { font-size: 22px; color: #059669; margin-bottom: 4px; }');
    printWindow.document.write('.header p { color: #6b7280; font-size: 12px; }');
    printWindow.document.write('.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }');
    printWindow.document.write('.info-label { font-size: 10px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }');
    printWindow.document.write('.info-value { font-size: 13px; color: #374151; }');
    printWindow.document.write('.info-value.bold { font-weight: 700; color: #111827; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }');
    printWindow.document.write('thead th { background: #f3f4f6; padding: 8px 12px; text-align: left; font-size: 11px; font-weight: 600; color: #4b5563; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }');
    printWindow.document.write('thead th.right { text-align: right; }');
    printWindow.document.write('thead th.center { text-align: center; }');
    printWindow.document.write('tbody td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }');
    printWindow.document.write('tbody td.right { text-align: right; }');
    printWindow.document.write('tbody td.center { text-align: center; }');
    printWindow.document.write('tbody td.bold { font-weight: 600; }');
    printWindow.document.write('tfoot td { padding: 10px 12px; font-weight: 700; font-size: 14px; border-top: 2px solid #e5e7eb; }');
    printWindow.document.write('tfoot td.right { text-align: right; }');
    printWindow.document.write('.total-row { color: #059669; }');
    printWindow.document.write('.status-badge { display: inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }');
    printWindow.document.write('.delivered-note { margin-top: 16px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; font-size: 12px; color: #047857; }');
    printWindow.document.write('.footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 11px; color: #9ca3af; }');
    printWindow.document.write('.cover-badge { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 9px; font-weight: 700; background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; margin-left: 6px; }');
    printWindow.document.write('@media print { body { padding: 20px; } }');
    printWindow.document.write('</style></head><body>');

    // Parse data from the invoice area
    var order = null;
    window.allOrders.forEach(function(o) {
        if (o.dispatch_key === dispatchKey) order = o;
    });

    if (!order) { printWindow.close(); return; }

    var phaseLabel = order.phase_number ? ' &mdash; Phase ' + order.phase_number + ' of ' + order.total_phases : '';

    printWindow.document.write('<div class="header">');
    printWindow.document.write('<h1>Canson School &amp; Office Supplies</h1>');
    printWindow.document.write('<p>Delivery Invoice' + phaseLabel + '</p>');
    printWindow.document.write('</div>');

    printWindow.document.write('<div class="info-grid">');
    printWindow.document.write('<div><div class="info-label">Order Number</div><div class="info-value bold">' + order.order_id + (order.phase_number ? ' (Phase ' + order.phase_number + ')' : '') + '</div></div>');
    printWindow.document.write('<div><div class="info-label">Customer</div><div class="info-value bold">' + order.customer + '</div></div>');
    printWindow.document.write('<div><div class="info-label">Contact</div><div class="info-value">' + (order.contact || '—') + '</div></div>');
    printWindow.document.write('<div><div class="info-label">Delivery Address</div><div class="info-value">' + (order.address || '—') + '</div></div>');
    printWindow.document.write('<div><div class="info-label">Delivery Date</div><div class="info-value">' + order.delivery_date + '</div></div>');
    printWindow.document.write('<div><div class="info-label">Status</div><div class="info-value"><span class="status-badge">' + order.status + '</span></div></div>');
    printWindow.document.write('</div>');

    // Items table
    printWindow.document.write('<table>');
    printWindow.document.write('<thead><tr><th>Item</th><th class="center">Qty</th><th class="right">Unit Price</th><th class="right">Subtotal</th></tr></thead>');
    printWindow.document.write('<tbody>');

    var grandTotal = 0;
    order.item_details.forEach(function(item) {
        var lineTotal = item.quantity * item.unit_price;
        grandTotal += lineTotal;
        var coverBadge = item.is_cover ? '<span class="cover-badge">COVER</span>' : '';
        printWindow.document.write('<tr>');
        printWindow.document.write('<td>' + item.name + coverBadge + '</td>');
        printWindow.document.write('<td class="center">' + item.quantity + '</td>');
        printWindow.document.write('<td class="right">&#8369;' + item.unit_price.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>');
        printWindow.document.write('<td class="right bold">&#8369;' + lineTotal.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>');
        printWindow.document.write('</tr>');
    });

    printWindow.document.write('</tbody>');
    printWindow.document.write('<tfoot><tr><td colspan="3" class="right">Total</td><td class="right total-row">&#8369;' + grandTotal.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td></tr></tfoot>');
    printWindow.document.write('</table>');

    if (order.status === 'Delivered') {
        printWindow.document.write('<div class="delivered-note">&#10003; Delivered on ' + order.delivered_at + '</div>');
    }

    printWindow.document.write('<div class="footer">');
    printWindow.document.write('<p>Canson School &amp; Office Supplies &mdash; Internal Delivery Invoice</p>');
    printWindow.document.write('<p>Printed on ' + new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' }) + '</p>');
    printWindow.document.write('</div>');

    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() { printWindow.print(); }, 300);
}

// ========== Deliver Order ==========
window.deliverOrder = function(dispatchKey, btn) {
    if (!confirm('Mark this order as delivered?')) return;

    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Delivering...';

    var order = window.allOrders.find(function(o) { return o.dispatch_key === dispatchKey; });
    if (order && order.item_details && order.item_details.length > 0) {
        openDamageModal(dispatchKey, order);
    } else {
        submitDelivery(dispatchKey, [], btn, originalText);
    }
}

// ========== Damage Report Modal ==========
var currentDeliveryDispatchKey = null;

window.openDamageModal = function(dispatchKey, order) {
    currentDeliveryDispatchKey = dispatchKey;

    document.getElementById('dmgOrderRef').textContent = order.order_id + ' — ' + order.customer;

    var body = document.getElementById('dmgItemsBody');
    body.innerHTML = '';

    order.item_details.forEach(function(item, index) {
        var isCover = item.is_cover || false;
        var row = document.createElement('tr');
        row.className = 'dmg-item-row border-b border-gray-100' + (isCover ? ' bg-amber-50/50' : '');
        row.innerHTML =
            '<td class="px-4 py-3">' +
                '<label class="flex items-center gap-2 cursor-pointer">' +
                    '<input type="checkbox" class="dmg-check w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500" data-index="' + index + '" data-item-id="' + item.product_id + '" data-item-name="' + item.name + '" onchange="toggleDamageRow(this)">' +
                    '<span class="text-sm font-medium text-gray-900">' + item.name + '</span>' +
                '</label>' +
                (isCover ? '<span class="ml-6 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-800"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>DAMAGE COVER — FREE</span>' : '') +
            '</td>' +
            '<td class="px-4 py-3 text-sm text-gray-500 text-center">' + item.quantity + '</td>' +
            '<td class="px-4 py-3">' +
                '<input type="number" aria-label="Damage quantity for ' + item.name + '" class="dmg-qty w-full px-2 py-1.5 border border-gray-200 rounded text-sm text-center focus:outline-none focus:ring-1 focus:ring-red-500 disabled:bg-gray-50 disabled:text-gray-300" disabled min="1" max="' + item.quantity + '" value="1">' +
            '</td>' +
            '<td class="px-4 py-3">' +
                '<input type="text" aria-label="Damage reason for ' + item.name + '" class="dmg-reason w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-red-500 disabled:bg-gray-50 disabled:text-gray-300" disabled placeholder="e.g. Crushed in transit">' +
            '</td>';
        body.appendChild(row);
    });

    var modal = document.getElementById('damageModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

window.toggleDamageRow = function(checkbox) {
    var row = checkbox.closest('tr');
    var qtyInput = row.querySelector('.dmg-qty');
    var reasonInput = row.querySelector('.dmg-reason');
    if (checkbox.checked) {
        qtyInput.disabled = false;
        reasonInput.disabled = false;
        reasonInput.required = true;
        row.classList.add('bg-red-50');
    } else {
        qtyInput.disabled = true;
        reasonInput.disabled = true;
        reasonInput.required = false;
        qtyInput.value = 1;
        reasonInput.value = '';
        row.classList.remove('bg-red-50');
    }
}

window.closeDamageModal = function() {
    document.getElementById('damageModal').style.display = 'none';
    document.body.style.overflow = '';
    currentDeliveryDispatchKey = null;
    var btns = document.querySelectorAll('.deliver-btn');
    btns.forEach(function(b) { b.disabled = false; b.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Deliver'; });
}

window.skipDamageReport = function() {
    submitDelivery(currentDeliveryDispatchKey, []);
}

window.submitDamageReport = function() {
    var damages = [];
    var rows = document.querySelectorAll('.dmg-item-row');
    var valid = true;

    rows.forEach(function(row) {
        var check = row.querySelector('.dmg-check');
        if (check.checked) {
            var qty = parseInt(row.querySelector('.dmg-qty').value);
            var reason = row.querySelector('.dmg-reason').value.trim();

            if (!reason) {
                row.querySelector('.dmg-reason').classList.add('border-red-400');
                valid = false;
                return;
            }
            row.querySelector('.dmg-reason').classList.remove('border-red-400');

            damages.push({
                item_id:   parseInt(check.getAttribute('data-item-id')),
                item_name: check.getAttribute('data-item-name'),
                quantity:  qty,
                reason:    reason,
            });
        }
    });

    if (!valid) { alert('Please provide a damage reason for all checked items.'); return; }

    if (damages.length === 0) {
        submitDelivery(currentDeliveryDispatchKey, []);
        return;
    }

    submitDelivery(currentDeliveryDispatchKey, damages);
}

window.submitDelivery = function(dispatchKey, damages, btn, originalText) {
    var submitBtn = document.getElementById('dmgSubmitBtn');
    var skipBtn = document.getElementById('dmgSkipBtn');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Processing...'; }
    if (skipBtn) { skipBtn.disabled = true; }

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Find the order entry by dispatch_key
    var order = window.allOrders.find(function(o) { return o.dispatch_key === dispatchKey; });
    if (!order) { alert('Order not found.'); return; }

    var phaseId = (order.current_phase) ? order.current_phase.id : null;

    var payload = { order_id: order.id, damages: damages };
    if (phaseId) payload.phase_id = phaseId;

    fetch('/dispatch/deliver', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            closeDamageModal();
            window.location.reload();
        } else {
            alert(data.message || 'Failed to deliver order.');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit & Deliver'; }
            if (skipBtn) { skipBtn.disabled = false; }
            if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('An error occurred.');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit & Deliver'; }
        if (skipBtn) { skipBtn.disabled = false; }
        if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
    });
}

// ========== Tab Filter ==========
var currentDispatchTab = 'Ready for Delivery';

window.filterDispatch = function() {
    var search = document.getElementById('dispatchSearch').value.toLowerCase();

    document.querySelectorAll('.dispatch-card').forEach(function(card) {
        var matchSearch = !search || card.getAttribute('data-search').includes(search);
        var matchStatus = card.getAttribute('data-status') === currentDispatchTab;
        card.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
};

document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    var dispatchTabs = document.querySelectorAll('.dispatch-tab-btn');
    dispatchTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            dispatchTabs.forEach(function(t) {
                t.className = 'dispatch-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50';
            });
            tab.className = 'dispatch-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white';
            currentDispatchTab = tab.getAttribute('data-status');
            filterDispatch();
        });
    });

    // Apply default filter on load
    filterDispatch();
});
