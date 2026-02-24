{{-- Assignment Details Modal --}}
<div id="assignmentDetailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAssignmentDetailsModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Assignment Details</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Full information about this assignment</p>
                </div>
                <button onclick="closeAssignmentDetailsModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-5">
                {{-- Order & Status Header --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                            </svg>
                        </div>
                        <div>
                            <h4 id="detailOrderId" class="text-lg font-bold text-gray-900"></h4>
                            <p id="detailAssignedDate" class="text-xs text-gray-500"></p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span id="detailPriorityBadge" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border"></span>
                        <span id="detailStatusBadge" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border"></span>
                    </div>
                </div>

                {{-- Customer Information --}}
                <div class="bg-gray-50 rounded-xl p-5">
                    <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Customer Information
                    </h5>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Customer Name</label>
                            <p id="detailCustomer" class="text-sm font-semibold text-gray-900"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Contact Number</label>
                            <p id="detailContact" class="text-sm text-gray-700"></p>
                        </div>
                    </div>
                </div>

                {{-- Delivery Information --}}
                <div class="bg-gray-50 rounded-xl p-5">
                    <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H18.75M3.375 14.25h1.5m0 0h12.75m0 0l-1.5-8.625a.75.75 0 00-.735-.625H7.61a.75.75 0 00-.735.625L5.25 14.25"/></svg>
                        Delivery Details
                    </h5>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Delivery Date</label>
                            <p id="detailDeliveryDate" class="text-sm font-medium text-gray-900"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Total Amount</label>
                            <p id="detailTotalAmount" class="text-sm font-bold text-emerald-600"></p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Delivery Address</label>
                        <p id="detailAddress" class="text-sm text-gray-700"></p>
                    </div>
                </div>

                {{-- Order Items --}}
                <div class="bg-gray-50 rounded-xl p-5">
                    <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                        Order Items
                    </h5>
                    <p id="detailItems" class="text-sm font-medium text-gray-900"></p>
                </div>

                {{-- Notes --}}
                <div id="detailNotesSection" class="hidden">
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <h5 class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            Assignment Notes
                        </h5>
                        <p id="detailNotes" class="text-sm text-amber-900"></p>
                    </div>
                </div>

                {{-- Assignment Metadata --}}
                <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                    <p id="detailAssignedBy" class="text-xs text-gray-500"></p>
                    <p id="detailAssignmentId" class="text-xs text-gray-400"></p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
                <button onclick="closeAssignmentDetailsModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Close
                </button>
                <button id="detailUpdateStatusBtn" onclick="closeAssignmentDetailsModal(); openUpdateStatusModal(currentDetailAssignment);" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                    Update Status
                </button>
            </div>
        </div>
    </div>
</div>
