{{-- Stock In Batch Detail Modal --}}
<div id="stockInDetailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeStockInDetailModal()"></div>

    {{-- Modal Panel --}}
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden max-h-[90vh]">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 bg-emerald-50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Batch Stock In Details</h3>
                    <p class="text-xs text-gray-500 font-mono" id="detailModalRef">—</p>
                </div>
            </div>
            <button onclick="closeStockInDetailModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-emerald-100 text-gray-400 hover:text-gray-600 transition-colors">
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
                    <p class="text-xs text-gray-400 mb-0.5">Date Received</p>
                    <p class="text-sm font-semibold text-gray-800" id="detailModalDate">—</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Supplier</p>
                    <p class="text-sm font-semibold text-gray-800" id="detailModalSupplier">—</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Performed By</p>
                    <p class="text-sm font-semibold text-gray-800" id="detailModalCreatedBy">—</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Notes</p>
                    <p class="text-sm font-semibold text-gray-800 truncate" id="detailModalNotes">—</p>
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
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Qty Added</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">New Stock</th>
                            </tr>
                        </thead>
                        <tbody id="detailModalItemsBody" class="divide-y divide-gray-100">
                            {{-- Populated via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end flex-shrink-0">
            <button type="button" onclick="closeStockInDetailModal()"
                class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
