// Orders Page JavaScript
// =====================
// All orders page functions. inventoryItems is injected by the blade template.

// ========== Order Details Modal ==========
window.showOrderDetails = function(orderId, order) {
    document.getElementById('modalOrderId').textContent = `Order ${order.id}`;
    document.getElementById('modalCustomerName').textContent = order.customer;
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
    order.order_items.forEach(item => {
        itemsHTML += `
            <tr>
                <td class="px-4 py-2 text-sm text-gray-900">${item.name}</td>
                <td class="px-4 py-2 text-sm text-gray-600 text-right">${item.qty}</td>
                <td class="px-4 py-2 text-sm text-gray-600 text-right">₱${item.price.toFixed(2)}</td>
                <td class="px-4 py-2 text-sm font-semibold text-gray-900 text-right">₱${item.subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            </tr>
        `;
    });
    document.getElementById('modalOrderItems').innerHTML = itemsHTML;

    // Total
    document.getElementById('modalTotalAmount').textContent = `₱${order.total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    // Notes
    document.getElementById('modalNotes').textContent = order.notes || 'No notes provided';

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
    document.getElementById('addOrderItemsBody').innerHTML = createOrderItemRow(0);
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
    document.getElementById('addOrderModal').classList.remove('hidden');
};

window.closeAddOrderModal = function() {
    document.getElementById('addOrderModal').classList.add('hidden');
};

function createOrderItemRow(index) {
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
    tbody.insertAdjacentHTML('beforeend', createOrderItemRow(orderItemIndex));
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
        const qty   = parseFloat(row.querySelector('.item-qty')?.value)   || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        const subtotal = qty * price;
        total += subtotal;
        const subtotalCell = row.querySelector('.item-subtotal');
        if (subtotalCell) {
            subtotalCell.textContent = '₱' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Highlight qty input if exceeds stock
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
        items: []
    };

    document.querySelectorAll('#addOrderItemsBody .order-item-row').forEach(row => {
        const name  = row.querySelector('select')?.value;
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

    // Validate stock availability
    var stockError = false;
    document.querySelectorAll('#addOrderItemsBody .order-item-row').forEach(row => {
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
function showOrderToast(message) {
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
