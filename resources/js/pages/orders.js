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
    document.getElementById('addOrderModal').classList.remove('hidden');
};

window.closeAddOrderModal = function() {
    document.getElementById('addOrderModal').classList.add('hidden');
};

function createOrderItemRow(index) {
    const items = window.inventoryItems || [];
    let optionsHtml = '<option value="">-- Select item --</option>';
    items.forEach(item => {
        optionsHtml += `<option value="${item.name}">${item.name}</option>`;
    });

    return `
        <tr class="order-item-row">
            <td class="px-4 py-2">
                <select name="items[${index}][name]" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    ${optionsHtml}
                </select>
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

    console.log('New Order Data:', orderData);

    closeAddOrderModal();
    showOrderToast('Order created successfully!');
};

// ========== Toast ==========
function showOrderToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-6 right-6 z-[60] bg-emerald-600 text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2 text-sm font-medium animate-slide-up';
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
