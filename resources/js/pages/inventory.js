// Inventory Page JavaScript

// ========== Tab Switching ==========
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.inv-tab-btn');
    const tabPanels = {
        'inv-stock':   document.getElementById('tab-inv-stock'),
        'inv-reports': document.getElementById('tab-inv-reports'),
    };
    const headerTitle    = document.getElementById('inv-page-title');
    const headerSubtitle = document.getElementById('inv-page-subtitle');
    const headerMap = {
        'inv-stock':   { title: 'Inventory Management', subtitle: 'Track stock levels across all items' },
        'inv-reports': { title: 'Inventory Reports',    subtitle: 'Stock valuation, low stock alerts, and movement summary' },
    };

    function activateInvTab(tabKey) {
        tabs.forEach(t => { t.className = 'inv-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50'; });
        Object.values(tabPanels).forEach(p => { if (p) p.classList.add('hidden'); });
        const btn = [...tabs].find(t => t.dataset.tab === tabKey);
        const panel = tabPanels[tabKey];
        if (btn) btn.className = 'inv-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white';
        if (panel) panel.classList.remove('hidden');
        const header = headerMap[tabKey];
        if (header) {
            if (headerTitle)    headerTitle.textContent    = header.title;
            if (headerSubtitle) headerSubtitle.textContent = header.subtitle;
        }
        localStorage.setItem('inv-active-tab', tabKey);
    }

    tabs.forEach(tab => { tab.addEventListener('click', () => activateInvTab(tab.dataset.tab)); });
    const saved = localStorage.getItem('inv-active-tab');
    if (saved && tabPanels[saved]) activateInvTab(saved);
});

// ========== Inventory Filter ==========
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

// ========== Toast (legacy – now handled by global toast module) ==========
function showInventoryToast(message, color = 'green') {
    if (color === 'red') {
        toast.error(message);
    } else {
        toast.success(message);
    }
}

// ========== Add Product Modal ==========
window.openAddProductModal = function () {
    // Reset all fields
    document.getElementById('addProductName').value = '';
    document.getElementById('addProductCategory').value = '';
    document.getElementById('addProductUnit').value = '';
    document.getElementById('addProductPrice').value = '';
    document.getElementById('addProductStock').value = '';
    document.getElementById('addProductReorderPoint').value = '50';
    const statusEl = document.getElementById('addProductStatus');
    if (statusEl) statusEl.value = 'In Stock';
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

    if (!name)     { toast.warning('Please enter a product name.'); return; }
    if (!category) { toast.warning('Please select a category.'); return; }
    if (!unit)     { toast.warning('Please select a unit.'); return; }
    if (!price || parseFloat(price) < 0) { toast.warning('Please enter a valid unit price.'); return; }
    if (!stock || parseInt(stock) < 0)   { toast.warning('Please enter a valid initial stock.'); return; }

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
