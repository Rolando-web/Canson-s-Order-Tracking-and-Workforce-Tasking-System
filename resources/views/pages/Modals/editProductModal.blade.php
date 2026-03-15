{{-- Edit Product Modal --}}
<div id="editProductModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEditProductModal()"></div>

    {{-- Modal Content --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10">
        {{-- Header --}}
        <div class="sticky top-0 bg-emerald-600 rounded-t-2xl px-6 py-4 flex items-center justify-between z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Edit Product</h3>
                    <p class="text-emerald-100 text-xs">Update product details</p>
                </div>
            </div>
            <button onclick="closeEditProductModal()" class="text-white/70 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form --}}
        <div class="p-6 space-y-5">
            <input type="hidden" id="editProductId">

            {{-- Product Name --}}
            <div>
                <label for="editProductName" class="block text-sm font-semibold text-gray-700 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                <input type="text" id="editProductName" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    placeholder="e.g. Data Filer Box (Blue)">
            </div>

            {{-- Unit Price --}}
            <div>
                <label for="editProductPrice" class="block text-sm font-semibold text-gray-700 mb-1.5">Unit Price (₱) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">₱</span>
                    <input type="number" id="editProductPrice" required min="0" step="0.01"
                        class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        placeholder="0.00">
                </div>
            </div>

            {{-- Low Stock Threshold --}}
            <div>
                <label for="editProductReorderPoint" class="block text-sm font-semibold text-gray-700 mb-1.5">Low Stock Threshold</label>
                <div class="flex items-center gap-3">
                    <input type="number" id="editProductReorderPoint" min="1"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        placeholder="50">
                </div>
                <p class="text-xs text-gray-400 mt-1">Alert when stock drops below this number</p>
            </div>

            {{-- Product Image --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Product Image <span class="text-gray-400 font-normal">(optional)</span></label>
                <div id="editProductImageDrop" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-emerald-400 transition-colors cursor-pointer" onclick="document.getElementById('editProductImage').click()">
                    <div id="editProductImagePreview" class="hidden mb-3">
                        <img id="editProductImagePreviewImg" src="" alt="Preview" class="w-20 h-20 rounded-lg object-cover mx-auto border border-gray-200">
                    </div>
                    <div id="editProductImagePlaceholder">
                        <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75z"/>
                        </svg>
                        <p class="text-sm text-gray-500">Click to upload a new image</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG up to 2MB</p>
                    </div>
                    <input type="file" id="editProductImage" accept="image/*" class="hidden" onchange="previewEditProductImage(event)">
                </div>
            </div>

            {{-- Info note --}}
            <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <p class="text-xs text-amber-700">Stock quantities can only be adjusted through the <strong>Inventory</strong> page via Stock In / Stock Out.</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeEditProductModal()" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="submitEditProduct()" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Save Changes
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
