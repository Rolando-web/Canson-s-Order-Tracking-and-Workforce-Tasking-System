{{-- Sale / Order View Details Modal --}}
<div id="saleDetailModal"
     class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900" id="modalOrderId">Order Details</h3>
                <p class="text-xs text-gray-400 mt-0.5" id="modalOrderDate">—</p>
            </div>
            <button onclick="closeSaleModal()"
                    class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3 mb-4">
                <div id="modalInitial"
                     class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700 flex-shrink-0">
                    C
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900" id="modalCustomer">—</p>
                    <p class="text-xs text-gray-400" id="modalContact">—</p>
                </div>
                <span id="modalStatusBadge"
                      class="ml-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Amount</p>
                    <p class="font-bold text-gray-900" id="modalAmount">₱0</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-0.5">Priority</p>
                    <p class="font-medium text-gray-700" id="modalPriority">—</p>
                </div>
            </div>
            <div id="modalAddressDiv" class="mt-3 bg-gray-50 rounded-lg p-3 text-sm hidden">
                <p class="text-xs text-gray-400 mb-0.5">Delivery Address</p>
                <p class="text-gray-700" id="modalAddress">—</p>
            </div>
            <div id="modalNotesDiv" class="mt-3 bg-amber-50 border border-amber-100 rounded-lg p-3 text-sm hidden">
                <p class="text-xs text-amber-600 mb-0.5 font-medium">Notes</p>
                <p class="text-gray-700" id="modalNotes">—</p>
            </div>
        </div>
        <div class="p-6">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Order Items</p>
            <div id="modalItems" class="space-y-2">
                <p class="text-sm text-gray-400">Loading…</p>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-base font-bold text-gray-900" id="modalTotal">₱0</p>
            </div>
        </div>
    </div>
</div>
