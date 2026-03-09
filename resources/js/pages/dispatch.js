// ========== Order Detail Modal (show/hide only — content rendered by Blade) ==========
window.openDetailModal = function(orderId) {
    var modal = document.getElementById('dispatchDetailModal-' + orderId);
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

window.closeDetailModal = function(orderId) {
    var modal = document.getElementById('dispatchDetailModal-' + orderId);
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// ========== Deliver Order ==========
window.deliverOrder = function(orderId, btn) {
    if (!confirm('Mark this order as delivered?')) return;

    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Delivering...';

    var order = window.allOrders.find(function(o) { return o.id === orderId; });
    if (order && order.item_details && order.item_details.length > 0) {
        openDamageModal(orderId, order);
    } else {
        submitDelivery(orderId, [], btn, originalText);
    }
}

// ========== Damage Report Modal ==========
var currentDeliveryOrderId = null;

window.openDamageModal = function(orderId, order) {
    currentDeliveryOrderId = orderId;

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
                '<input type="number" class="dmg-qty w-full px-2 py-1.5 border border-gray-200 rounded text-sm text-center focus:outline-none focus:ring-1 focus:ring-red-500 disabled:bg-gray-50 disabled:text-gray-300" disabled min="1" max="' + item.quantity + '" value="1">' +
            '</td>' +
            '<td class="px-4 py-3">' +
                '<input type="text" class="dmg-reason w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-red-500 disabled:bg-gray-50 disabled:text-gray-300" disabled placeholder="e.g. Crushed in transit">' +
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
    currentDeliveryOrderId = null;
    var btns = document.querySelectorAll('.deliver-btn');
    btns.forEach(function(b) { b.disabled = false; b.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Deliver'; });
}

window.skipDamageReport = function() {
    submitDelivery(currentDeliveryOrderId, []);
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
        submitDelivery(currentDeliveryOrderId, []);
        return;
    }

    submitDelivery(currentDeliveryOrderId, damages);
}

window.submitDelivery = function(orderId, damages, btn, originalText) {
    var submitBtn = document.getElementById('dmgSubmitBtn');
    var skipBtn = document.getElementById('dmgSkipBtn');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Processing...'; }
    if (skipBtn) { skipBtn.disabled = true; }

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/dispatch/deliver', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ order_id: orderId, damages: damages }),
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
