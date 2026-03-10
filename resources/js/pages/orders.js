// Orders Page JavaScript
// =====================
// All orders page functions. inventoryItems is injected by the blade template.

// ========== Order Details Modal ==========
window.showOrderDetails = function(orderId) {
    const order = (window.ordersData || {})[orderId];
    if (!order) return;
    document.getElementById('modalOrderId').textContent = `Order ${order.id}`;
    document.getElementById('modalCustomerName').textContent = order.customer;
    const nameBody = document.getElementById('modalCustomerNameBody');
    if (nameBody) nameBody.textContent = order.customer;
    document.getElementById('modalCustomerContact').textContent = order.contact;
    document.getElementById('modalDeliveryDate').textContent = new Date(order.delivery_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('modalDeliveryAddress').textContent = order.address;

    // Status badge
    const statusColors = {
        'Completed': 'bg-green-50 text-green-600 border-green-200',
        'In-Progress': 'bg-emerald-50 text-emerald-600 border-emerald-200',
        'Pending': 'bg-gray-50 text-gray-500 border-gray-200'
    };
    document.getElementById('modalStatus').innerHTML = `<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border ${statusColors[order.status] || 'bg-gray-50 text-gray-500 border-gray-200'}">${order.status}</span>`;

    // Priority badge
    document.getElementById('modalPriority').innerHTML = `<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border ${order.priorityColor}">${order.priority}</span>`;

    // Assigned
    document.getElementById('modalAssigned').textContent = order.assigned || 'Unassigned';

    // Order items table
    let itemsHTML = '';
    (order.order_items || []).forEach(item => {
        const completed = item.completed_qty || 0;
        const total     = item.qty || 0;
        const pct       = total > 0 ? Math.round((completed / total) * 100) : 0;
        const barColor  = pct >= 100 ? 'bg-emerald-500' : pct > 0 ? 'bg-blue-400' : 'bg-gray-200';
        const textColor = pct >= 100 ? 'text-emerald-600' : pct > 0 ? 'text-blue-500' : 'text-gray-400';
        itemsHTML += `
            <tr>
                <td class="px-4 py-2 text-sm text-gray-900">
                    ${item.name}
                    <div class="mt-1 flex items-center gap-2">
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="${barColor} h-full rounded-full transition-all" style="width:${pct}%"></div>
                        </div>
                        <span class="text-xs font-medium ${textColor} whitespace-nowrap">${completed}/${total} (${pct}%)</span>
                    </div>
                </td>
                <td class="px-4 py-2 text-sm text-gray-600 text-right">${item.qty}</td>
                <td class="px-4 py-2 text-sm text-gray-600 text-right">₱${parseFloat(item.price).toFixed(2)}</td>
                <td class="px-4 py-2 text-sm font-semibold text-gray-900 text-right">₱${parseFloat(item.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            </tr>
        `;
    });
    document.getElementById('modalOrderItems').innerHTML = itemsHTML;

    // Total
    document.getElementById('modalTotalAmount').textContent = `₱${parseFloat(order.total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    // Notes
    document.getElementById('modalNotes').textContent = order.notes || 'No notes provided';

    // Delivery Phases
    const phasesSection = document.getElementById('modalPhasesSection');
    const phasesContainer = document.getElementById('modalPhasesContainer');
    const phases = order.phases || [];
    if (phases.length === 0) {
        phasesSection.classList.add('hidden');
    } else {
        phasesSection.classList.remove('hidden');
        const statusColors = {
            'Pending':   'bg-gray-100 text-gray-600',
            'In-Progress': 'bg-blue-50 text-blue-600',
            'Completed': 'bg-green-50 text-green-600',
            'Delivered': 'bg-emerald-50 text-emerald-600',
        };
        phasesContainer.innerHTML = phases.map(phase => {
            const deliveryDate = new Date(phase.delivery_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            const badge = statusColors[phase.status] || 'bg-gray-100 text-gray-600';
            const itemRows = (phase.items || []).map(pi => {
                const pct = pi.required_qty > 0 ? Math.round((pi.completed_qty / pi.required_qty) * 100) : 0;
                return `
                    <tr>
                        <td class="px-3 py-2 text-sm text-gray-900">${pi.name}</td>
                        <td class="px-3 py-2 text-sm text-gray-600 text-right">${pi.required_qty}</td>
                        <td class="px-3 py-2 text-sm text-gray-600 text-right">${pi.completed_qty}</td>
                        <td class="px-3 py-2 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full ${pct >= 100 ? 'bg-green-500' : 'bg-blue-500'}" style="width:${pct}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-8 text-right">${pct}%</span>
                            </div>
                        </td>
                    </tr>`;
            }).join('');
            return `
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between bg-gray-50 border-b border-gray-200 px-4 py-2.5">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-800">Phase ${phase.number}</span>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${badge}">${phase.status}</span>
                        </div>
                        <span class="text-xs text-gray-500">📅 ${deliveryDate}</span>
                    </div>
                    <table class="w-full">
                        <thead class="bg-white border-b border-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Item</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Required</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Completed</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">${itemRows}</tbody>
                    </table>
                </div>`;
        }).join('');
    }

    document.getElementById('orderDetailsModal').classList.remove('hidden');
};

window.closeOrderDetails = function() {
    document.getElementById('orderDetailsModal').classList.add('hidden');
};

// ========== Add Order Modal ==========
let orderItemIndex = 1;

window.openAddOrderModal = function() {
    document.getElementById('addOrderForm').reset();
    orderItemIndex = 1;
    window.pendingCoverClaimIds = [];

    // Reset items table: remove all rows except the first, reset first row
    const tbody = document.getElementById('addOrderItemsBody');
    const allRows = tbody.querySelectorAll('.order-item-row');
    allRows.forEach((row, i) => {
        if (i === 0) {
            // Reset first row values (keep the PHP-rendered options intact)
            const sel = row.querySelector('select');
            if (sel) sel.value = '';
            const qty = row.querySelector('.item-qty');
            if (qty) qty.value = 1;
            const price = row.querySelector('.item-price');
            if (price) price.value = 0;
            const subtotal = row.querySelector('.item-subtotal');
            if (subtotal) subtotal.textContent = '₱0.00';
            const indicator = row.querySelector('.stock-indicator');
            if (indicator) indicator.textContent = '';
        } else {
            row.remove();
        }
    });
    // Reset phases
    if (typeof window.resetPhases === 'function') window.resetPhases();
    // Hide damage claims alert
    var dcAlert = document.getElementById('damageClaimsAlert');
    if (dcAlert) dcAlert.classList.add('hidden');
    // Hide customer dropdown
    var custDd = document.getElementById('customerSuggestionsDropdown');
    if (custDd) { custDd.classList.add('hidden'); custDd.innerHTML = ''; }
    recalcOrderTotal();
    // Set min date to today so past dates are disabled
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const dateInput = document.getElementById('deliveryDateInput');
    if (dateInput) {
        dateInput.min = `${yyyy}-${mm}-${dd}`;
    }
    // Reset delivery mode — phases always visible
    document.getElementById('addOrderModal').classList.remove('hidden');
};

window.closeAddOrderModal = function() {
    document.getElementById('addOrderModal').classList.add('hidden');
    if (typeof window.resetPhases === 'function') window.resetPhases();
};

window.createOrderItemRow = function(index) {
    const items = window.inventoryItems || [];
    let optionsHtml = '<option value="">-- Select item --</option>';
    items.forEach(item => {
        optionsHtml += `<option value="${item.name}" data-price="${item.unit_price}" data-stock="${item.stock}">${item.name} (${item.stock} in stock)</option>`;
    });

    return `
        <tr class="order-item-row">
            <td class="px-4 py-2">
                <select name="items[${index}][name]" required onchange="onItemSelected(this)"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    ${optionsHtml}
                </select>
                <div class="stock-indicator mt-1 text-xs"></div>
            </td>
            <td class="px-4 py-2">
                <input type="number" name="items[${index}][qty]" required min="1" value="1"
                    class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-emerald-500 item-qty"
                    oninput="recalcOrderTotal()">
            </td>
            <td class="px-4 py-2">
                <input type="number" name="items[${index}][price]" required min="0" step="0.01" value="0"
                    class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-emerald-500 item-price"
                    oninput="recalcOrderTotal()">
            </td>
            <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900 item-subtotal">₱0.00</td>
            <td class="px-4 py-2 text-center">
                <button type="button" onclick="removeOrderItem(this)" class="text-gray-300 hover:text-red-500 transition-colors" title="Remove item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </td>
        </tr>
    `;
}

window.addOrderItem = function() {
    const tbody = document.getElementById('addOrderItemsBody');
    // Clone the select options from the very first row (PHP-rendered, always has full list)
    const firstSelect = tbody.querySelector('select');
    let optionsHtml = '<option value="">-- Select item --</option>';
    if (firstSelect) {
        Array.from(firstSelect.options).forEach(opt => {
            if (opt.value) {
                optionsHtml += `<option value="${opt.value}" data-price="${opt.getAttribute('data-price') || 0}" data-stock="${opt.getAttribute('data-stock') || 0}">${opt.text}</option>`;
            }
        });
    }
    const row = `
        <tr class="order-item-row">
            <td class="px-4 py-2">
                <select name="items[${orderItemIndex}][name]" required onchange="onItemSelected(this)"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    ${optionsHtml}
                </select>
                <div class="stock-indicator mt-1 text-xs"></div>
            </td>
            <td class="px-4 py-2">
                <input type="number" name="items[${orderItemIndex}][qty]" required min="1" value="1"
                    class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-emerald-500 item-qty"
                    oninput="recalcOrderTotal()">
            </td>
            <td class="px-4 py-2">
                <input type="number" name="items[${orderItemIndex}][price]" required min="0" step="0.01" value="0"
                    class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-emerald-500 item-price"
                    oninput="recalcOrderTotal()">
            </td>
            <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900 item-subtotal">₱0.00</td>
            <td class="px-4 py-2 text-center">
                <button type="button" onclick="removeOrderItem(this)" class="text-gray-300 hover:text-red-500 transition-colors" title="Remove item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </td>
        </tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
    orderItemIndex++;
};

window.removeOrderItem = function(btn) {
    const tbody = document.getElementById('addOrderItemsBody');
    if (tbody.querySelectorAll('.order-item-row').length > 1) {
        btn.closest('tr').remove();
        recalcOrderTotal();
    }
};

window.recalcOrderTotal = function() {
    const rows = document.querySelectorAll('#addOrderItemsBody .order-item-row');
    let total = 0;
    rows.forEach(row => {
        const isCover = row.classList.contains('cover-item-row');
        const qty   = parseFloat(row.querySelector('.item-qty')?.value)   || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        const subtotal = qty * price;
        total += subtotal;
        const subtotalCell = row.querySelector('.item-subtotal');
        if (subtotalCell) {
            if (isCover) {
                subtotalCell.textContent = 'FREE';
            } else {
                subtotalCell.textContent = '₱' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        // Highlight qty input if exceeds stock (skip cover rows)
        if (!isCover) {
            const sel = row.querySelector('select');
            const qtyInput = row.querySelector('.item-qty');
            if (sel && sel.value && qtyInput) {
                const stock = parseInt(sel.options[sel.selectedIndex].getAttribute('data-stock')) || 0;
                if (qty > stock) {
                    qtyInput.classList.add('border-red-400', 'ring-1', 'ring-red-400', 'bg-red-50');
                    qtyInput.classList.remove('border-gray-200');
                } else {
                    qtyInput.classList.remove('border-red-400', 'ring-1', 'ring-red-400', 'bg-red-50');
                    qtyInput.classList.add('border-gray-200');
                }
            }
        }
    });
    document.getElementById('addOrderTotal').textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

window.submitAddOrder = function(event) {
    event.preventDefault();
    const form = document.getElementById('addOrderForm');
    const formData = new FormData(form);

    const orderData = {
        customer_name:    formData.get('customer_name'),
        contact_number:   formData.get('contact_number'),
        delivery_address: formData.get('delivery_address'),
        delivery_date:    formData.get('delivery_date'),
        priority:         formData.get('priority'),
        notes:            formData.get('notes'),
        items: [],
        cover_claim_ids: window.pendingCoverClaimIds || []
    };

    document.querySelectorAll('#addOrderItemsBody .order-item-row').forEach(row => {
        const isCover = row.classList.contains('cover-item-row');
        // For cover rows the select is disabled, so read from hidden input or select
        let name;
        if (isCover) {
            const hiddenInput = row.querySelector('input[type="hidden"]');
            name = hiddenInput ? hiddenInput.value : row.querySelector('select')?.value;
        } else {
            name = row.querySelector('select')?.value;
        }
        const qty   = parseFloat(row.querySelector('.item-qty')?.value)   || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        if (name && qty > 0) {
            orderData.items.push({ name, qty, price, subtotal: qty * price });
        }
    });

    if (orderData.items.length === 0) {
        alert('Please add at least one item to the order.');
        return;
    }

    // Collect phases if any exist; otherwise backend auto-creates Phase 1
    const phaseCards = document.querySelectorAll('.phase-card');
    if (phaseCards.length > 0) {
        orderData.phases = [];
        phaseCards.forEach((card, ci) => {
            const deliveryDate = card.querySelector('input[type="date"]')?.value;
            const phaseItems = [];
            card.querySelectorAll('.phase-item-row').forEach(row => {
                const nameInput = row.querySelector('input[type="hidden"]');
                const qtyInput  = row.querySelector('.phase-qty-input');
                const name = nameInput ? nameInput.value : '';
                const qty  = parseInt(qtyInput?.value) || 0;
                if (name && qty > 0) phaseItems.push({ name, qty });
            });
            orderData.phases.push({ delivery_date: deliveryDate, items: phaseItems });
        });

        // Use Phase 1 date as order delivery date if phases exist
        const phase1Date = phaseCards[0].querySelector('input[type="date"]')?.value;
        if (phase1Date) {
            orderData.delivery_date = phase1Date;
        }
    }

    // Validate stock availability (skip cover items — they use price=0)
    var stockError = false;
    document.querySelectorAll('#addOrderItemsBody .order-item-row').forEach(row => {
        if (row.classList.contains('cover-item-row')) return; // skip cover rows
        const sel = row.querySelector('select');
        if (sel && sel.value) {
            const opt = sel.options[sel.selectedIndex];
            const stock = parseInt(opt.getAttribute('data-stock')) || 0;
            const qty = parseInt(row.querySelector('.item-qty')?.value) || 0;
            if (qty > stock) {
                stockError = true;
                alert('"' + sel.value + '" only has ' + stock + ' in stock. Please reduce the quantity.');
            }
        }
    });
    if (stockError) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAddOrderModal();
            showOrderToast('Order created successfully!');
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || 'Failed to create order.');
        }
    })
    .catch(err => {
        console.error('Order creation error:', err);
        alert('Something went wrong. Please try again.');
    });
};

// ========== Auto-fill Unit Price ==========
window.onItemSelected = function(selectEl) {
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
    const row = selectEl.closest('.order-item-row');
    const priceInput = row.querySelector('.item-price');
    if (priceInput) {
        priceInput.value = price.toFixed(2);
    }

    // Show stock indicator
    const indicator = row.querySelector('.stock-indicator');
    if (indicator) {
        if (!selectedOption.value) {
            indicator.innerHTML = '';
        } else if (stock <= 0) {
            indicator.innerHTML = '<span class="inline-flex items-center gap-1 text-red-600 font-medium"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg> Out of stock</span>';
        } else if (stock <= 10) {
            indicator.innerHTML = '<span class="inline-flex items-center gap-1 text-amber-600 font-medium"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="4"/></svg> Low stock: ' + stock + ' available</span>';
        } else {
            indicator.innerHTML = '<span class="inline-flex items-center gap-1 text-emerald-600 font-medium"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="4"/></svg> In stock: ' + stock + ' available</span>';
        }
    }

    // Set max qty to available stock
    const qtyInput = row.querySelector('.item-qty');
    if (qtyInput && selectedOption.value) {
        qtyInput.setAttribute('max', stock);
        if (parseInt(qtyInput.value) > stock) {
            qtyInput.value = stock > 0 ? stock : 1;
        }
    }

    recalcOrderTotal();
};

// ========== Toast ==========
window.showOrderToast = function(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-6 right-6 z-[60] bg-emerald-600 text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2 text-sm font-medium animate-slide-up';
    toast.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        ${message}
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ========== Filter Orders ==========
window.filterOrders = function() {
    var search = document.getElementById('orderSearch').value.toLowerCase();
    var status = document.getElementById('orderStatusFilter').value;
    document.querySelectorAll('.order-row').forEach(function(row) {
        var matchSearch = !search || row.getAttribute('data-search').includes(search);
        var matchStatus = !status || row.getAttribute('data-status') === status;
        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}

// ========== Customer Autocomplete ==========
var customerSearchTimeout = null;

window.onCustomerNameInput = function(value) {
    clearTimeout(customerSearchTimeout);
    var dropdown = document.getElementById('customerSuggestionsDropdown');

    if (!value || value.trim().length < 2) {
        dropdown.classList.add('hidden');
        dropdown.innerHTML = '';
        return;
    }

    customerSearchTimeout = setTimeout(function() {
        fetch('/orders/customer-suggestions?q=' + encodeURIComponent(value.trim()), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(customers) {
            if (customers.length === 0) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                return;
            }

            dropdown.innerHTML = customers.map(function(c) {
                return '<button type="button" onclick="selectCustomer(\'' + c.name.replace(/'/g, "\\'") + '\', \'' + (c.contact || '').replace(/'/g, "\\'") + '\', \'' + (c.address || '').replace(/'/g, "\\'") + '\')" ' +
                    'class="w-full text-left px-3 py-2 hover:bg-emerald-50 flex items-center gap-2 transition-colors border-b border-gray-100 last:border-0">' +
                    '<div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-xs font-bold flex-none">' + c.name.charAt(0).toUpperCase() + '</div>' +
                    '<div class="min-w-0">' +
                    '<p class="text-sm font-medium text-gray-900 truncate">' + c.name + '</p>' +
                    '<p class="text-xs text-gray-400 truncate">' + (c.contact || '') + ' · ' + (c.address || '').substring(0, 30) + '</p>' +
                    '</div>' +
                    '</button>';
            }).join('');
            dropdown.classList.remove('hidden');
        })
        .catch(function() { dropdown.classList.add('hidden'); });
    }, 250);
}

window.selectCustomer = function(name, contact, address) {
    document.getElementById('orderCustomerName').value = name;
    document.getElementById('customerSuggestionsDropdown').classList.add('hidden');

    // Auto-fill contact and address
    var contactInput = document.querySelector('input[name="contact_number"]');
    var addressInput = document.querySelector('input[name="delivery_address"]');
    if (contactInput && contact) contactInput.value = contact;
    if (addressInput && address) addressInput.value = address;

    // Check for pending damage claims
    checkDamageClaims(name);
};

window.hideCustomerDropdown = function() {
    document.getElementById('customerSuggestionsDropdown').classList.add('hidden');
}

// ========== Damage Claims Lookup + Auto-Add Cover Items ==========
var damageCheckTimeout = null;
window.checkDamageClaims = function(name) {
    clearTimeout(damageCheckTimeout);
    var alert = document.getElementById('damageClaimsAlert');
    var list = document.getElementById('damageClaimsList');

    // Clean up any existing cover rows
    removeCoverRows();
    window.pendingCoverClaimIds = [];

    if (!name || name.trim().length < 2) {
        alert.classList.add('hidden');
        return;
    }

    damageCheckTimeout = setTimeout(function() {
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('/returns/pending-for-customer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ customer_name: name.trim() }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.claims && data.claims.length > 0) {
                // Show alert
                list.innerHTML = data.claims.map(function(c) {
                    return '<div class="flex items-center justify-between bg-white rounded px-2.5 py-1.5 border border-amber-200">' +
                        '<div class="flex items-center gap-2">' +
                        '<span class="text-xs font-medium text-amber-700">' + c.return_id + '</span>' +
                        '<span class="text-xs text-gray-700 font-semibold">' + c.item_name + '</span>' +
                        '<span class="text-xs text-gray-500">x' + c.quantity + '</span>' +
                        '</div>' +
                        '<span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">AUTO-ADDED</span>' +
                        '</div>';
                }).join('');
                alert.classList.remove('hidden');

                // Auto-add cover items to order table
                data.claims.forEach(function(c) {
                    addCoverItemRow(c);
                    window.pendingCoverClaimIds.push(c.id);
                });

                recalcOrderTotal();
            } else {
                alert.classList.add('hidden');
            }
        })
        .catch(function() { alert.classList.add('hidden'); });
    }, 300);
}

// Add a cover item row (non-removable, price=0, with COVER badge)
window.addCoverItemRow = function(claim) {
    var tbody = document.getElementById('addOrderItemsBody');
    var items = window.inventoryItems || [];

    // Find the inventory item to get its name for the select
    var matchedItem = null;
    for (var i = 0; i < items.length; i++) {
        if (items[i].id === claim.item_id) {
            matchedItem = items[i];
            break;
        }
    }

    if (!matchedItem) return;

    var idx = tbody.querySelectorAll('.order-item-row').length;

    var row = '<tr class="order-item-row cover-item-row bg-amber-50/50" data-claim-id="' + claim.id + '">' +
        '<td class="px-4 py-2">' +
            '<div class="flex items-center gap-2">' +
                '<select name="items[' + idx + '][name]" required onchange="onItemSelected(this)" ' +
                    'class="w-full px-3 py-2.5 border border-amber-300 rounded-lg text-sm bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-400" disabled>' +
                    '<option value="' + matchedItem.name + '" selected data-price="0" data-stock="' + matchedItem.stock + '">' + matchedItem.name + '</option>' +
                '</select>' +
            '</div>' +
            '<div class="mt-1 flex items-center gap-1.5">' +
                '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-800">' +
                    '<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>' +
                    'DAMAGE COVER — ' + claim.return_id +
                '</span>' +
            '</div>' +
            '<input type="hidden" name="items[' + idx + '][name]" value="' + matchedItem.name + '">' +
        '</td>' +
        '<td class="px-4 py-2">' +
            '<input type="number" name="items[' + idx + '][qty]" required min="1" value="' + claim.quantity + '" ' +
                'class="w-full px-2 py-1.5 border border-amber-300 rounded text-sm text-right bg-amber-50 focus:outline-none item-qty" readonly>' +
        '</td>' +
        '<td class="px-4 py-2">' +
            '<input type="number" name="items[' + idx + '][price]" required min="0" step="0.01" value="0" ' +
                'class="w-full px-2 py-1.5 border border-amber-300 rounded text-sm text-right bg-amber-50 focus:outline-none item-price" readonly>' +
        '</td>' +
        '<td class="px-4 py-2 text-right text-sm font-semibold text-amber-700 item-subtotal">FREE</td>' +
        '<td class="px-4 py-2 text-center">' +
            '<span class="text-amber-400" title="Auto-added damage cover item">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>' +
            '</span>' +
        '</td>' +
    '</tr>';

    tbody.insertAdjacentHTML('beforeend', row);
}

window.removeCoverRows = function() {
    document.querySelectorAll('.cover-item-row').forEach(function(row) {
        row.remove();
    });
}

// ========== Phases Builder ==========
var phaseCount = 0;

// Read current order items from the table
window.getOrderItems = function() {
    var rows = document.querySelectorAll('#addOrderItemsBody .order-item-row:not(.cover-item-row)');
    var items = [];
    rows.forEach(function(row) {
        var nameEl = row.querySelector('select[name*="[name]"]') || row.querySelector('input[name*="[name]"]');
        var qtyEl  = row.querySelector('input[name*="[qty]"]');
        if (!nameEl || !qtyEl) return;
        var name = nameEl.value ? nameEl.value.trim() : '';
        var qty  = parseInt(qtyEl.value) || 0;
        if (name && qty > 0) items.push({ name: name, qty: qty });
    });
    return items;
}

// Sum qty allocated in ALL phases for a given item index
window.getAllocatedQty = function(itemIndex) {
    var total = 0;
    document.querySelectorAll('.phase-card').forEach(function(card) {
        card.querySelectorAll('.phase-item-row').forEach(function(row) {
            if (parseInt(row.dataset.itemIndex) === itemIndex) {
                total += parseInt(row.querySelector('.phase-qty-input').value) || 0;
            }
        });
    });
    return total;
}

// Auto-clamp a phase qty input so total allocated never exceeds order qty
window.clampPhaseQty = function(input) {
    var row = input.closest('.phase-item-row');
    if (!row) return;
    var itemIndex = parseInt(row.dataset.itemIndex);
    var orderItems = getOrderItems();
    var orderQty = (orderItems[itemIndex] || {}).qty || 0;
    if (orderQty === 0) return;

    // Sum allocated by OTHER phase cards for this item
    var thisCard = input.closest('.phase-card');
    var otherAllocated = 0;
    document.querySelectorAll('.phase-card').forEach(function(card) {
        if (card === thisCard) return;
        card.querySelectorAll('.phase-item-row').forEach(function(r) {
            if (parseInt(r.dataset.itemIndex) === itemIndex) {
                otherAllocated += parseInt(r.querySelector('.phase-qty-input').value) || 0;
            }
        });
    });

    var maxAllowed = Math.max(0, orderQty - otherAllocated);
    var current = parseInt(input.value) || 0;
    if (current > maxAllowed) {
        input.value = maxAllowed;
    }
}

window.enforcePhaseDateOrder = function() {
    var cards = document.querySelectorAll('.phase-card');
    var prevDate = null;
    cards.forEach(function(card) {
        var input = card.querySelector('input[type="date"]');
        if (!input) return;
        if (prevDate) {
            // min must be at least one day after the previous phase date
            var d = new Date(prevDate);
            d.setDate(d.getDate() + 1);
            var minStr = d.toISOString().split('T')[0];
            input.min = minStr;
            // If current value violates the new min, clear it
            if (input.value && input.value <= prevDate) {
                input.value = '';
            }
        }
        if (input.value) prevDate = input.value;
    });
}

window.updatePhaseWarnings = function() {
    enforcePhaseDateOrder();
    var orderItems = getOrderItems();
    var warnings = [];
    orderItems.forEach(function(oi, idx) {
        var allocated = getAllocatedQty(idx);
        if (allocated > oi.qty) {
            warnings.push('<strong>' + oi.name + '</strong>: allocated ' + allocated + ' but order only has ' + oi.qty);
        }
    });
    var warnDiv = document.getElementById('phaseQtyWarning');
    if (warnings.length) {
        warnDiv.innerHTML = '⚠ Over-allocated: ' + warnings.join('; ');
        warnDiv.classList.remove('hidden');
    } else {
        warnDiv.classList.add('hidden');
    }
    // Also refresh remaining hints on all phase item rows
    document.querySelectorAll('.phase-item-row').forEach(function(row) {
        var itemIndex = parseInt(row.dataset.itemIndex);
        var orderQty = (orderItems[itemIndex] || {}).qty || 0;
        var allocated = getAllocatedQty(itemIndex);
        var hint = row.querySelector('.phase-qty-hint');
        if (hint) {
            var remaining = orderQty - allocated;
            hint.textContent = allocated + ' of ' + orderQty + ' allocated' + (remaining < 0 ? ' (over by ' + Math.abs(remaining) + ')' : '');
            hint.className = 'phase-qty-hint text-xs mt-0.5 ' + (remaining < 0 ? 'text-red-500 font-semibold' : 'text-gray-400');
        }
    });
}

// Generate a unique invoice number for a phase
function generatePhaseInvoice(phaseNumber) {
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var rand = String(Math.floor(1000 + Math.random() * 9000));
    return 'INV-' + year + month + day + '-P' + phaseNumber + '-' + rand;
}

window.addPhase = function() {
    if (document.querySelectorAll('.phase-card').length >= 5) {
        alert('Maximum of 5 phases allowed.');
        return;
    }

    var orderItems = getOrderItems();
    if (orderItems.length === 0) {
        alert('Please add Order Items first before adding a phase.');
        return;
    }

    phaseCount++;
    var pi = document.querySelectorAll('.phase-card').length; // zero-based index
    var container = document.getElementById('phasesContainer');
    var totalPhases = pi + 1;

    // Calculate date: today + (phaseNumber * 7 days)
    var phaseDate = new Date();
    phaseDate.setDate(phaseDate.getDate() + (totalPhases * 7));
    var dateStr = phaseDate.toISOString().split('T')[0];

    // Generate unique invoice number for this phase
    var invoiceNum = generatePhaseInvoice(totalPhases);

    // Build item rows with placeholder qty (will be set by redistributePhases)
    var rowsHtml = '';
    orderItems.forEach(function(item, ii) {
        rowsHtml +=
            '<tr class="phase-item-row" data-item-name="' + item.name.replace(/"/g, '&quot;') + '" data-item-index="' + ii + '">' +
                '<td class="px-3 py-2">' +
                    '<div class="text-sm font-medium text-gray-800">' + item.name + '</div>' +
                    '<input type="hidden" name="phases[' + pi + '][items][' + ii + '][name]" value="' + item.name.replace(/"/g, '&quot;') + '">' +
                '</td>' +
                '<td class="px-3 py-2 w-32">' +
                    '<input type="number" name="phases[' + pi + '][items][' + ii + '][qty]" ' +
                        'value="0" min="0" max="' + item.qty + '" ' +
                        'class="phase-qty-input w-full px-2 py-1.5 border border-gray-200 rounded-lg text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-400" ' +
                        'oninput="clampPhaseQty(this);updatePhaseWarnings()">' +
                    '<div class="phase-qty-hint text-xs mt-0.5 text-gray-400"></div>' +
                '</td>' +
            '</tr>';
    });

    var card = document.createElement('div');
    card.className = 'phase-card border border-indigo-200 rounded-xl bg-white overflow-hidden shadow-sm';
    card.dataset.phaseIndex = pi;
    card.innerHTML =
        '<div class="flex flex-wrap items-center justify-between px-4 py-2.5 bg-indigo-50 border-b border-indigo-200 gap-2">' +
            '<div class="flex flex-wrap items-center gap-2 sm:gap-3">' +
                '<span class="phase-label text-xs font-bold text-indigo-700 uppercase tracking-wide">Phase ' + phaseCount + '</span>' +
                '<div class="flex items-center gap-1.5">' +
                    '<label class="text-[11px] text-indigo-500">Delivery Date</label>' +
                    '<input type="date" name="phases[' + pi + '][delivery_date]" required ' +
                        'value="' + dateStr + '" ' +
                        'onchange="enforcePhaseDateOrder()" ' +
                        'class="px-2 py-1 border border-indigo-300 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">' +
                '</div>' +
                '<div class="flex items-center gap-1.5">' +
                    '<label class="text-[11px] text-indigo-500">Invoice #</label>' +
                    '<input type="text" name="phases[' + pi + '][invoice_number]" ' +
                        'value="' + invoiceNum + '" ' +
                        'class="phase-invoice-input px-2 py-1 border border-indigo-300 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white w-36" ' +
                        'readonly>' +
                '</div>' +
            '</div>' +
            '<button type="button" onclick="removePhase(this)" class="text-indigo-300 hover:text-red-500 transition-colors ml-2" title="Remove phase">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
            '</button>' +
        '</div>' +
        '<div class="p-3">' +
            '<table class="w-full">' +
                '<thead>' +
                    '<tr class="border-b border-gray-100">' +
                        '<th class="px-3 py-1.5 text-left text-xs font-semibold text-gray-500">Item</th>' +
                        '<th class="px-3 py-1.5 text-right text-xs font-semibold text-gray-500 w-32">Qty to Deliver</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody class="divide-y divide-gray-50">' + rowsHtml + '</tbody>' +
            '</table>' +
        '</div>';

    container.appendChild(card);
    redistributePhases();
}

window.removePhase = function(btn) {
    btn.closest('.phase-card').remove();
    renumberPhases();
    redistributePhases();
}

// Auto-redistribute quantities evenly across all phases and set dates 1 week apart
window.redistributePhases = function() {
    var orderItems = getOrderItems();
    var cards = document.querySelectorAll('.phase-card');
    var totalPhases = cards.length;

    if (totalPhases === 0 || orderItems.length === 0) return;

    // Set dates: today + (phaseNumber * 7 days)
    var today = new Date();
    cards.forEach(function(card, ci) {
        var dateInput = card.querySelector('input[type="date"]');
        if (dateInput) {
            var phaseDate = new Date(today);
            phaseDate.setDate(phaseDate.getDate() + ((ci + 1) * 7));
            dateInput.value = phaseDate.toISOString().split('T')[0];
        }
    });

    // Split quantities evenly across phases
    orderItems.forEach(function(item, ii) {
        var totalQty = item.qty;
        var baseQty = Math.floor(totalQty / totalPhases);
        var remainder = totalQty % totalPhases;

        cards.forEach(function(card, ci) {
            // First 'remainder' phases get +1 extra
            var qty = baseQty + (ci < remainder ? 1 : 0);
            var input = card.querySelector('.phase-item-row[data-item-index="' + ii + '"] .phase-qty-input');
            if (input) input.value = qty;
        });
    });

    updatePhaseWarnings();
}

window.renumberPhases = function() {
    phaseCount = 0;
    document.querySelectorAll('.phase-card').forEach(function(card, ci) {
        phaseCount++;
        card.dataset.phaseIndex = ci;
        var label = card.querySelector('.phase-label');
        if (label) label.textContent = 'Phase ' + phaseCount;
        // Regenerate invoice number for new phase numbering
        var invoiceInput = card.querySelector('.phase-invoice-input');
        if (invoiceInput) invoiceInput.value = generatePhaseInvoice(phaseCount);
        card.querySelectorAll('[name]').forEach(function(el) {
            el.name = el.name.replace(/phases\[\d+\]/, 'phases[' + ci + ']');
        });
    });
}

// Reset phases when modal closes
window.resetPhases = function() {
    phaseCount = 0;
    document.getElementById('phasesContainer').innerHTML = '';
    document.getElementById('phaseQtyWarning').classList.add('hidden');
}
