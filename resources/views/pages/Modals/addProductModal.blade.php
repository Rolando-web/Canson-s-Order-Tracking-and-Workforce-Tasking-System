{{-- Add Product Modal --}}
<div id="addProductModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAddProductModal()"></div>

    {{-- Modal Content --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto z-10">
        {{-- Header --}}
        <div class="sticky top-0 bg-emerald-600 rounded-t-2xl px-6 py-4 flex items-center justify-between z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Add New Product</h3>
                    <p class="text-emerald-100 text-xs">Fill in the product details below</p>
                </div>
            </div>
            <button onclick="closeAddProductModal()" class="text-white/70 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form --}}
        <div id="addProductForm" class="p-6 space-y-5">

            {{-- Product Name --}}
            <div>
                <label for="addProductName" class="block text-sm font-semibold text-gray-700 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                <input type="text" id="addProductName" name="name" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    placeholder="e.g. Data Filer Box (Blue)">
            </div>

            {{-- Category & Unit (side by side) --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="addProductCategory" class="block text-sm font-semibold text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                    <select id="addProductCategory" name="category" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">Select category</option>
                        <option value="Finished Goods">Finished Goods</option>
                        <option value="Raw Materials">Raw Materials</option>
                        <option value="Packaging Materials">Packaging Materials</option>
                    </select>
                </div>
                <div>
                    <label for="addProductUnit" class="block text-sm font-semibold text-gray-700 mb-1.5">Unit <span class="text-red-500">*</span></label>
                    <select id="addProductUnit" name="unit" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">Select unit</option>
                        <option value="pcs">Pieces (pcs)</option>
                        <option value="reams">Reams</option>
                        <option value="sheets">Sheets</option>
                        <option value="liters">Liters</option>
                        <option value="kg">Kilograms (kg)</option>
                        <option value="rolls">Rolls</option>
                        <option value="boxes">Boxes</option>
                    </select>
                </div>
            </div>

            {{-- Unit Price & Initial Stock (side by side) --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="addProductPrice" class="block text-sm font-semibold text-gray-700 mb-1.5">Unit Price (₱) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">₱</span>
                        <input type="number" id="addProductPrice" name="unit_price" required min="0" step="0.01"
                            class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            placeholder="0.00">
                    </div>
                </div>
                <div>
                    <label for="addProductStock" class="block text-sm font-semibold text-gray-700 mb-1.5">Initial Stock <span class="text-red-500">*</span></label>
                    <input type="number" id="addProductStock" name="stock" required min="0"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        placeholder="0">
                </div>
            </div>

            {{-- Status --}}
            <div>
                <label for="addProductStatus" class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                <select id="addProductStatus" name="status"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="In Stock">In Stock</option>
                    <option value="Low Stock">Low Stock</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>

            {{-- Product Image --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Product Image <span class="text-gray-400 font-normal">(optional)</span></label>
                <div id="addProductImageDrop" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-emerald-400 transition-colors cursor-pointer" onclick="document.getElementById('addProductImage').click()">
                    <div id="addProductImagePreview" class="hidden mb-3">
                        <img id="addProductImagePreviewImg" src="" alt="Preview" class="w-20 h-20 rounded-lg object-cover mx-auto border border-gray-200">
                    </div>
                    <div id="addProductImagePlaceholder">
                        <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75z"/>
                        </svg>
                        <p class="text-sm text-gray-500">Click to upload an image</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG up to 2MB</p>
                    </div>
                    <input type="file" id="addProductImage" name="image" accept="image/*" class="hidden" onchange="previewAddProductImage(event)">
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeAddProductModal()" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="submitAddProduct()" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add Product
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
