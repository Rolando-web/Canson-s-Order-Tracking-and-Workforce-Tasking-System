@extends('partials.app', ['title' => 'Products - Canson', 'activePage' => 'products'])

@push('styles')
<style>
    .product-card {
        transition: all 0.2s ease;
    }
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<div class="products-page">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Products</h2>
            <p class="text-gray-500 mt-1">Manage and add products to your inventory</p>
        </div>
        <button onclick="openAddProductModal()"
            class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Add New Product
        </button>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" id="productSearch" placeholder="Search products..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        </div>
        <select id="categoryFilter"
            class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">All Categories</option>
            @foreach($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
            @endforeach
        </select>
        <select id="statusFilter"
            class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">All Status</option>
            <option value="In Stock">In Stock</option>
            <option value="Low Stock">Low Stock</option>
            <option value="Out of Stock">Out of Stock</option>
        </select>
    </div>

    {{-- Products Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" id="productsGrid">
        @forelse($items as $item)
        <div class="product-card bg-white rounded-xl border border-gray-200 overflow-hidden"
             data-name="{{ strtolower($item->name) }}"
             data-category="{{ $item->category }}"
             data-status="{{ $item->status }}">
            {{-- Product Image --}}
            <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden">
                @if($item->image_path)
                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}"
                        class="w-full h-full object-cover">
                @else
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                @endif
            </div>

            {{-- Product Info --}}
            <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 leading-tight">{{ $item->name }}</h3>
                    <div class="flex items-center gap-1.5">
                        @if($item->is_best_seller)
                            <span class="flex-shrink-0 px-2 py-0.5 text-[0.65rem] font-bold bg-amber-100 text-amber-700 rounded-full">BEST</span>
                        @endif
                        <button onclick="openEditProductModal({{ $item->Product_Id }}, '{{ addslashes($item->name) }}', {{ $item->unit_price ?? 0 }}, '{{ $item->image_path ? asset('storage/' . $item->image_path) : '' }}', {{ $item->reorder_point ?? 50 }})" 
                            class="flex-shrink-0 w-7 h-7 rounded-lg bg-gray-100 hover:bg-emerald-100 flex items-center justify-center transition-colors group"
                            title="Edit product">
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 mb-1">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[0.65rem] font-mono font-semibold bg-gray-100 text-gray-700 border border-gray-200">{{ $item->item_id }}</span>
                    <span class="text-xs text-gray-400">{{ $item->category }}</span>
                </div>
                <p class="text-sm font-bold text-emerald-600 mb-3">₱{{ number_format($item->unit_price ?? 0, 2) }}</p>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Stock</p>
                        <p class="text-sm font-bold text-gray-900">{{ $item->stock }} <span class="text-gray-400 font-normal">{{ $item->unit }}</span></p>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full
                        @if($item->status === 'In Stock') bg-emerald-100 text-emerald-700
                        @elseif($item->status === 'Low Stock') bg-amber-100 text-amber-700
                        @else bg-red-100 text-red-700
                        @endif">
                        {{ $item->status }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-400 mb-1">No products yet</h3>
            <p class="text-sm text-gray-400 mb-4">Add your first product to get started</p>
            <button onclick="openAddProductModal()"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Add New Product
            </button>
        </div>
        @endforelse
    </div>
</div>

{{-- Add Product Modal --}}
@include('pages.Modals.addProductModal')

{{-- Edit Product Modal --}}
@include('pages.Modals.editProductModal')
@endsection

@push('scripts')
<script>
function openEditProductModal(id, name, price, imageUrl, reorderPoint) {
    document.getElementById('editProductId').value = id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editProductPrice').value = price;
    document.getElementById('editProductReorderPoint').value = reorderPoint || 50;

    var preview = document.getElementById('editProductImagePreview');
    var previewImg = document.getElementById('editProductImagePreviewImg');
    var placeholder = document.getElementById('editProductImagePlaceholder');
    var fileInput = document.getElementById('editProductImage');
    if (fileInput) fileInput.value = '';
    if (imageUrl) {
        previewImg.src = imageUrl;
        if (preview) preview.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    } else {
        if (preview) preview.classList.add('hidden');
        if (placeholder) placeholder.classList.remove('hidden');
    }

    document.getElementById('editProductModal').classList.remove('hidden');
}
function closeEditProductModal() {
    document.getElementById('editProductModal').classList.add('hidden');
}
function openAddProductModal() {
    document.getElementById('addProductModal').classList.remove('hidden');
}
function closeAddProductModal() {
    document.getElementById('addProductModal').classList.add('hidden');
}
</script>
@vite('resources/js/pages/products.js')
@endpush
