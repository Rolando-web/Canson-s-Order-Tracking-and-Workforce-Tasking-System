{{-- Order Details Modal --}}
    <div id="orderDetailsModal" class="fixed inset-0 z-50 hidden">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeOrderDetails()"></div>
        
        {{-- Modal Content --}}
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative" onclick="event.stopPropagation()">
                {{-- Header --}}
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
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

                {{-- Content --}}
                <div class="px-6 py-5 space-y-6">
                    {{-- Customer Info --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Customer Contact</label>
                            <p class="text-sm text-gray-900" id="modalCustomerContact"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Delivery Date</label>
                            <p class="text-sm text-gray-900" id="modalDeliveryDate"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Delivery Address</label>
                        <p class="text-sm text-gray-900" id="modalDeliveryAddress"></p>
                    </div>

                    {{-- Order Status Info --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                            <span id="modalStatus"></span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Priority</label>
                            <span id="modalPriority"></span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Assigned To</label>
                            <p class="text-sm text-gray-900" id="modalAssigned"></p>
                        </div>
                    </div>

                    {{-- Order Items --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Order Items</label>
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Item Name</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Quantity</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Unit Price</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100" id="modalOrderItems">
                                    {{-- Dynamically populated --}}
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

                    {{-- Notes --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Notes</label>
                        <p class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3" id="modalNotes"></p>
                    </div>
                </div>