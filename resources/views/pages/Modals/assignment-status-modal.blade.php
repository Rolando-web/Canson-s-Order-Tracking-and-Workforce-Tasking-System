{{-- Update Assignment Status Modal --}}
<div id="updateStatusModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeUpdateStatusModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Update Status</h3>
                    <p class="text-sm text-gray-500">Change assignment status for <span id="statusOrderLabel" class="font-semibold text-emerald-600"></span></p>
                </div>
                <button onclick="closeUpdateStatusModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-4">
                {{-- Current Status --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Current Status</label>
                    <span id="currentStatusBadge" class="inline-flex px-3 py-1.5 rounded-full text-sm font-semibold border"></span>
                </div>

                {{-- New Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                    <div class="grid grid-cols-2 gap-3" id="statusOptions">
                        <button type="button" onclick="selectStatus('pending')" class="status-option flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-gray-400 transition-all" data-status="pending">
                            <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                            Pending
                        </button>
                        <button type="button" onclick="selectStatus('in_progress')" class="status-option flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-blue-400 transition-all" data-status="in_progress">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            In Progress
                        </button>
                        <button type="button" onclick="selectStatus('completed')" class="status-option flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-green-400 transition-all" data-status="completed">
                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            Completed
                        </button>
                        <button type="button" onclick="selectStatus('cancelled')" class="status-option flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-red-400 transition-all" data-status="cancelled">
                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            Cancelled
                        </button>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes (Optional)</label>
                    <textarea id="updateNotes" rows="2" placeholder="Add a note about this status change..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                <button onclick="closeUpdateStatusModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    Cancel
                </button>
                <button id="saveStatusBtn" onclick="saveStatusUpdate()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
