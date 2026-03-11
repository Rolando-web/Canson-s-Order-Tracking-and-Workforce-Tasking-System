{{-- Add / Edit Supplier Modal --}}
<div id="supplierModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeSupplierModal()"></div>

    {{-- Modal Panel --}}
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 bg-emerald-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900" id="supplierModalTitle">Add Supplier</h3>
                    <p class="text-xs text-gray-500">Manage your permanent suppliers</p>
                </div>
            </div>
            <button onclick="closeSupplierModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-emerald-100 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-4 overflow-y-auto max-h-[70vh]">
            <input type="hidden" id="supplierEditId" value="">

            {{-- Supplier Name --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Supplier Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="supplierName" placeholder="e.g. ABC Supplies Co." maxlength="50"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Address <span class="text-red-500">*</span>
                </label>
                <textarea id="supplierAddress" rows="2" placeholder="Full business address..."
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="supplierEmail" placeholder="supplier@email.com" maxlength="50"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Phone <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="supplierPhone" placeholder="09XXXXXXXXX" maxlength="11"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
            <button type="button" onclick="closeSupplierModal()"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button type="button" onclick="submitSupplier()" id="supplierSubmitBtn"
                class="px-5 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
                <span id="supplierSubmitText">Save Supplier</span>
            </button>
        </div>
    </div>
</div>
