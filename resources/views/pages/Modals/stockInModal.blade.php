{{-- Stock In Modal --}}
<div id="stockInModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeStockInModal()"></div>

    {{-- Modal Panel --}}
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 bg-green-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Stock In</h3>
                    <p class="text-xs text-gray-500">Add incoming stock to inventory</p>
                </div>
            </div>
            <button onclick="closeStockInModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-green-100 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-4 overflow-y-auto max-h-[70vh]">

            {{-- Item Info (read-only) --}}
            <div class="bg-gray-50 rounded-xl p-4 flex items-center gap-4 border border-gray-200">
                <div id="stockInItemImage" class="w-14 h-14 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0 overflow-hidden">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 truncate" id="stockInItemName">—</p>
                    <p class="text-xs text-gray-400 mt-0.5" id="stockInItemCode">Code: —</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-xs text-gray-500">Current Stock:</span>
                        <span class="text-sm font-bold text-gray-900" id="stockInCurrentStock">—</span>
                        <span class="text-xs text-gray-400" id="stockInUnit">—</span>
                    </div>
                </div>
            </div>

            {{-- Quantity --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Quantity to Add <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="adjustStockInQty(-1)" class="w-9 h-9 flex-shrink-0 flex items-center justify-center border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6"/></svg>
                    </button>
                    <input id="stockInQty" type="number" min="1" value="1" placeholder="0"
                        class="flex-1 text-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <button type="button" onclick="adjustStockInQty(1)" class="w-9 h-9 flex-shrink-0 flex items-center justify-center border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </button>
                </div>
            </div>

            {{-- New Stock Preview --}}
            <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-center justify-between">
                <span class="text-xs font-semibold text-green-700 uppercase tracking-wide">New Stock Level</span>
                <div class="flex items-center gap-1.5">
                    <span class="text-sm text-gray-500" id="stockInPreviewOld">—</span>
                    <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    <span class="text-base font-bold text-green-600" id="stockInPreviewNew">—</span>
                    <span class="text-xs text-green-500 font-medium" id="stockInPreviewDiff">(+0)</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Supplier / Source --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Supplier / Source</label>
                    <input type="text" id="stockInSupplier" placeholder="e.g. ABC Supplies Co."
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                {{-- Date Received --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date Received <span class="text-red-500">*</span></label>
                    <input type="date" id="stockInDate"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
            </div>

            {{-- Reference No. --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Reference No.</label>
                <input type="text" id="stockInReference" placeholder="Auto-generated" readonly
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none">
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Notes / Remarks</label>
                <textarea id="stockInNotes" rows="2" placeholder="e.g. Weekly supplier delivery, batch #123..."
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
            </div>
        </div>

        {{-- Footer --}}
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
            <button type="button" onclick="closeStockInModal()"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button type="button" onclick="submitStockIn()"
                class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Confirm Stock In
            </button>
        </div>
    </div>
</div>
