{{-- Stock Out Batch Detail Modal --}}
<div id="stockOutDetailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeStockOutDetailModal()"></div>

    {{-- Modal Panel --}}
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden max-h-[90vh]">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 bg-red-50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Batch Stock Out Details</h3>
                    <p class="text-xs text-gray-500 font-mono" id="soDetailModalRef">—</p>
                </div>
            </div>
            <button onclick="closeStockOutDetailModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-100 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 overflow-y-auto flex-1 space-y-4">

            {{-- Batch Meta Info --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Date</p>
                    <p class="text-sm font-semibold text-gray-800" id="soDetailModalDate">—</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Reason</p>
                    <p class="text-sm font-semibold text-gray-800" id="soDetailModalReason">—</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Performed By</p>
                    <p class="text-sm font-semibold text-gray-800" id="soDetailModalCreatedBy">—</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Notes</p>
                    <p class="text-sm font-semibold text-gray-800 truncate" id="soDetailModalNotes">—</p>
                </div>
            </div>

            {{-- Items Table --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Items in this Batch</p>
                <div class="rounded-xl border border-gray-200 overflow-hidden">
                    <table class="w-full min-w-[480px]">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Prev Stock</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Qty Removed</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">New Stock</th>
                            </tr>
                        </thead>
                        <tbody id="soDetailModalItemsBody" class="divide-y divide-gray-100">
                            {{-- Populated via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end flex-shrink-0">
            <button type="button" onclick="closeStockOutDetailModal()"
                class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
