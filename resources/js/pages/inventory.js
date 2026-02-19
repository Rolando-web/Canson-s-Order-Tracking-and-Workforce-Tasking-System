// Inventory Page JavaScript

// ========== Inventory Filter ==========
function generateRef(prefix) {
    const year = new Date().getFullYear();
    const rand = String(Math.floor(1000 + Math.random() * 9000));
    return `${prefix}-${year}-${rand}`;
}

window.filterInventory = function () {
    const search   = document.getElementById('inventorySearch')?.value.toLowerCase() ?? '';
    const category = document.getElementById('inventoryCategoryFilter')?.value ?? '';
    const stock    = document.getElementById('inventoryStockFilter')?.value ?? '';

    document.querySelectorAll('.inventory-row').forEach(row => {
        const name      = row.dataset.name ?? '';
        const rowCat    = row.dataset.category ?? '';
        const rowStock  = row.dataset.stockLevel ?? '';

        const matchSearch   = !search   || name.includes(search);
        const matchCategory = !category || rowCat === category;
        const matchStock    = !stock    || rowStock === stock;

        row.style.display = (matchSearch && matchCategory && matchStock) ? '' : 'none';
    });
};

// ========== Stock In Modal ==========
let _stockInCurrent = 0;

window.openStockInModal = function (item) {
    _stockInCurrent = item.stock ?? 0;

    // Populate item info
    document.getElementById('stockInItemName').textContent    = item.name  ?? '—';
    document.getElementById('stockInItemCode').textContent    = 'Code: ' + (item.code ?? 'N/A');
    document.getElementById('stockInCurrentStock').textContent = item.stock ?? '—';
    document.getElementById('stockInUnit').textContent        = item.unit  ?? '';

    // Image
    const imgBox = document.getElementById('stockInItemImage');
    if (item.image) {
        imgBox.innerHTML = `<img src="${item.image}" class="w-full h-full object-cover" alt="${item.name}">`;
    } else {
        imgBox.innerHTML = `<svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75z"/></svg>`;
    }

    // Reset fields
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
    const val   = Math.max(1, (parseInt(input.value) || 1) + delta);
    input.value = val;
    updateStockInPreview();
};

function updateStockInPreview() {
    const qty    = parseInt(document.getElementById('stockInQty')?.value) || 0;
    const newVal = _stockInCurrent + qty;
    document.getElementById('stockInPreviewOld').textContent  = _stockInCurrent.toLocaleString();
    document.getElementById('stockInPreviewNew').textContent  = newVal.toLocaleString();
    document.getElementById('stockInPreviewDiff').textContent = `(+${qty.toLocaleString()})`;
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('stockInQty')?.addEventListener('input', updateStockInPreview);
});

window.submitStockIn = function () {
    const qty  = parseInt(document.getElementById('stockInQty').value) || 0;
    const date = document.getElementById('stockInDate').value;
    if (qty < 1) { alert('Please enter a valid quantity.'); return; }
    if (!date)   { alert('Please select a date received.'); return; }

    console.log('Stock In submitted:', {
        qty,
        date,
        supplier:  document.getElementById('stockInSupplier').value,
        reference: document.getElementById('stockInReference').value,
        notes:     document.getElementById('stockInNotes').value,
    });

    closeStockInModal();
    showInventoryToast('Stock In recorded successfully!', 'green');
};

// ========== Stock Out Modal ==========
let _stockOutCurrent = 0;

window.openStockOutModal = function (item) {
    _stockOutCurrent = item.stock ?? 0;

    document.getElementById('stockOutItemName').textContent    = item.name  ?? '—';
    document.getElementById('stockOutItemCode').textContent    = 'Code: ' + (item.code ?? 'N/A');
    document.getElementById('stockOutCurrentStock').textContent = item.stock ?? '—';
    document.getElementById('stockOutUnit').textContent        = item.unit  ?? '';

    const imgBox = document.getElementById('stockOutItemImage');
    if (item.image) {
        imgBox.innerHTML = `<img src="${item.image}" class="w-full h-full object-cover" alt="${item.name}">`;
    } else {
        imgBox.innerHTML = `<svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75z"/></svg>`;
    }

    document.getElementById('stockOutQty').value       = 1;
    document.getElementById('stockOutReason').value    = '';
    document.getElementById('stockOutReference').value = generateRef('SO');
    document.getElementById('stockOutNotes').value     = '';
    document.getElementById('stockOutDate').value      = new Date().toISOString().split('T')[0];
    document.getElementById('stockOutQtyError').classList.add('hidden');

    updateStockOutPreview();
    document.getElementById('stockOutModal').classList.remove('hidden');
};

window.closeStockOutModal = function () {
    document.getElementById('stockOutModal').classList.add('hidden');
};

window.adjustStockOutQty = function (delta) {
    const input = document.getElementById('stockOutQty');
    const val   = Math.max(1, (parseInt(input.value) || 1) + delta);
    input.value = val;
    updateStockOutPreview();
};

window.updateStockOutPreview = function () {
    const qty    = parseInt(document.getElementById('stockOutQty')?.value) || 0;
    const newVal = _stockOutCurrent - qty;
    document.getElementById('stockOutPreviewOld').textContent  = _stockOutCurrent.toLocaleString();
    document.getElementById('stockOutPreviewNew').textContent  = Math.max(0, newVal).toLocaleString();
    document.getElementById('stockOutPreviewDiff').textContent = `(-${qty.toLocaleString()})`;

    const errEl = document.getElementById('stockOutQtyError');
    if (newVal < 0) {
        errEl.classList.remove('hidden');
    } else {
        errEl.classList.add('hidden');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('stockOutQty')?.addEventListener('input', updateStockOutPreview);
});

window.submitStockOut = function () {
    const qty    = parseInt(document.getElementById('stockOutQty').value) || 0;
    const reason = document.getElementById('stockOutReason').value;
    const date   = document.getElementById('stockOutDate').value;

    if (qty < 1)                    { alert('Please enter a valid quantity.'); return; }
    if (qty > _stockOutCurrent)     { alert('Quantity exceeds current stock.'); return; }
    if (!reason)                    { alert('Please select a reason.'); return; }
    if (!date)                      { alert('Please select a date.'); return; }

    console.log('Stock Out submitted:', {
        qty,
        reason,
        date,
        reference: document.getElementById('stockOutReference').value,
        notes:     document.getElementById('stockOutNotes').value,
    });

    closeStockOutModal();
    showInventoryToast('Stock Out recorded successfully!', 'red');
};

// ========== Toast ==========
function showInventoryToast(message, color = 'green') {
    const bg = color === 'red' ? 'bg-red-600' : 'bg-green-600';
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-[60] ${bg} text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2 text-sm font-medium`;
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

// ========== Add Product Modal ==========
window.openAddProductModal = function () {
    // Reset all fields
    document.getElementById('addProductName').value = '';
    document.getElementById('addProductCategory').value = '';
    document.getElementById('addProductUnit').value = '';
    document.getElementById('addProductPrice').value = '';
    document.getElementById('addProductStock').value = '';
    document.getElementById('addProductStatus').value = 'In Stock';
    document.getElementById('addProductImage').value = '';

    // Hide image preview, show placeholder
    const preview = document.getElementById('addProductImagePreview');
    const placeholder = document.getElementById('addProductImagePlaceholder');
    if (preview) preview.classList.add('hidden');
    if (placeholder) placeholder.classList.remove('hidden');

    document.getElementById('addProductModal').classList.remove('hidden');
};

window.closeAddProductModal = function () {
    document.getElementById('addProductModal').classList.add('hidden');
};

window.previewAddProductImage = function (event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        const preview = document.getElementById('addProductImagePreview');
        const placeholder = document.getElementById('addProductImagePlaceholder');
        const img = document.getElementById('addProductImagePreviewImg');

        img.src = e.target.result;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
};

window.submitAddProduct = function () {
    const name     = document.getElementById('addProductName').value.trim();
    const category = document.getElementById('addProductCategory').value;
    const unit     = document.getElementById('addProductUnit').value;
    const price    = document.getElementById('addProductPrice').value;
    const stock    = document.getElementById('addProductStock').value;

    if (!name)     { alert('Please enter a product name.'); return; }
    if (!category) { alert('Please select a category.'); return; }
    if (!unit)     { alert('Please select a unit.'); return; }
    if (!price || parseFloat(price) < 0) { alert('Please enter a valid unit price.'); return; }
    if (!stock || parseInt(stock) < 0)   { alert('Please enter a valid initial stock.'); return; }

    console.log('Add Product submitted (UI only):', {
        name,
        category,
        unit,
        unit_price: parseFloat(price),
        stock: parseInt(stock),
        status: document.getElementById('addProductStatus').value,
    });

    closeAddProductModal();
    showInventoryToast('Product added successfully!', 'green');
};
