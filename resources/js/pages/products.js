// Add Product Modal
window.openAddProductModal = function() {
    document.getElementById('addProductModal')?.classList.remove('hidden');
};

window.closeAddProductModal = function() {
    document.getElementById('addProductModal')?.classList.add('hidden');
};

// Edit Product Modal
window.openEditProductModal = function(id, name, price) {
    document.getElementById('editProductId').value = id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editProductPrice').value = price;
    document.getElementById('editProductModal')?.classList.remove('hidden');
};

window.closeEditProductModal = function() {
    document.getElementById('editProductModal')?.classList.add('hidden');
};

window.submitEditProduct = function() {
    const id = document.getElementById('editProductId')?.value;
    const name = document.getElementById('editProductName')?.value;
    const price = document.getElementById('editProductPrice')?.value;

    if (!name || price === '' || price === null) {
        alert('Please fill in all required fields.');
        return;
    }

    fetch(`/products/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, unit_price: price })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditProductModal();
            window.location.reload();
        } else {
            alert(data.message || 'Failed to update product.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the product.');
    });
};

// Preview Image
window.previewAddProductImage = function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('addProductImagePreview');
            const previewImg = document.getElementById('addProductImagePreviewImg');
            const placeholder = document.getElementById('addProductImagePlaceholder');
            previewImg.src = e.target.result;
            preview?.classList.remove('hidden');
            placeholder?.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
};

// Submit Add Product
window.submitAddProduct = function() {
    const form = document.getElementById('addProductForm');
    const formData = new FormData();

    const name = document.getElementById('addProductName')?.value;
    const category = document.getElementById('addProductCategory')?.value;
    const unit = document.getElementById('addProductUnit')?.value;
    const price = document.getElementById('addProductPrice')?.value;
    const stock = document.getElementById('addProductStock')?.value;
    const status = document.getElementById('addProductStatus')?.value;
    const image = document.getElementById('addProductImage')?.files[0];

    if (!name || !category || !unit || !price || !stock) {
        alert('Please fill in all required fields.');
        return;
    }

    formData.append('name', name);
    formData.append('category', category);
    formData.append('unit', unit);
    formData.append('unit_price', price);
    formData.append('stock', stock);
    formData.append('status', status);
    if (image) formData.append('image', image);

    fetch('/products', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                             document.querySelector('input[name="_token"]')?.value,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAddProductModal();
            window.location.reload();
        } else {
            alert(data.message || 'Failed to add product.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the product.');
    });
};

// Product filtering (runs when DOM is ready)
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const cards = document.querySelectorAll('.product-card');

    function filterProducts() {
        const search = searchInput.value.toLowerCase();
        const category = categoryFilter.value;
        const status = statusFilter.value;

        cards.forEach(card => {
            const matchName = card.dataset.name.includes(search);
            const matchCategory = !category || card.dataset.category === category;
            const matchStatus = !status || card.dataset.status === status;

            card.style.display = (matchName && matchCategory && matchStatus) ? '' : 'none';
        });
    }

    searchInput?.addEventListener('input', filterProducts);
    categoryFilter?.addEventListener('change', filterProducts);
    statusFilter?.addEventListener('change', filterProducts);
});
