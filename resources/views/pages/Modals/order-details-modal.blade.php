{{-- Order Details Modal --}}
<div id="orderDetailsModal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeOrderDetails()"></div>

    {{-- Centered Modal --}}
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative" onclick="event.stopPropagation()">

            {{-- Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
                <div>
                    <h3 class="text-xl font-bold text-gray-900" id="modalOrderId">Order Details</h3>
                    <p class="text-sm text-gray-500 mt-0.5" id="modalCustomerName"></p>
                </div>
                <button onclick="closeOrderDetails()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-5">

                {{-- Customer & Delivery Info --}}
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Customer</label>
                            <p class="text-sm font-semibold text-gray-900" id="modalCustomerNameBody"></p>
                            <p class="text-xs text-gray-500 mt-0.5" id="modalCustomerContact"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Delivery Date</label>
                            <p class="text-sm font-medium text-gray-900" id="modalDeliveryDate"></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        <p class="text-xs text-gray-600" id="modalDeliveryAddress"></p>
                    </div>
                </div>

                {{-- Status / Priority / Assigned --}}
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                        <span id="modalStatus"></span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Priority</label>
                        <span id="modalPriority"></span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Assigned To</label>
                        <p class="text-sm text-gray-900 font-medium" id="modalAssigned"></p>
                    </div>
                </div>

                {{-- Order Items Table --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Order Items</label>
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[500px]">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Item Name</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600">Qty</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600">Unit Price</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100" id="modalOrderItems">
                                    {{-- Populated via JS --}}
                                </tbody>
                                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-right text-sm font-bold text-gray-900">Total Amount:</td>
                                        <td class="px-4 py-3 text-right text-base font-bold text-emerald-600" id="modalTotalAmount"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Delivery Phases --}}
                <div id="modalPhasesSection">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Delivery Phases</label>
                    <div id="modalPhasesContainer" class="space-y-3">
                        {{-- Populated via JS --}}
                    </div>
                </div>

                {{-- Notes --}}
                <div id="modalNotesSection">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Notes</label>
                    <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3 border border-gray-200" id="modalNotes"></p>
                </div>

            </div>

            {{-- Footer --}}
            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end">
                <button onclick="closeOrderDetails()" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>
