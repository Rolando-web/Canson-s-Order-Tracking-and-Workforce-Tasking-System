@extends('partials.app', ['title' => 'My Assignments - Canson', 'activePage' => 'assignments'])

@push('styles')
    @vite('resources/css/pages/assignments.css')
@endpush


@section('content')
<div class="assignments-page">
    {{-- Notification Banner --}}
    @if($newAssignmentCount > 0)
    <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-bold text-amber-800">You have {{ $newAssignmentCount }} new assignment{{ $newAssignmentCount > 1 ? 's' : '' }} waiting!</p>
            <p class="text-xs text-amber-600 mt-0.5">Check below and start working on your pending tasks.</p>
        </div>
    </div>
    @endif

    {{-- Summary Cards --}}
    @php
        $pending = collect($myAssignments)->where('status', 'pending')->count();
        $inProgress = collect($myAssignments)->where('status', 'in_progress')->count();
        $completedWork = collect($myAssignments)->where('status', 'completed')->count();
        $total = count($myAssignments);
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase">Total Work</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $total }}</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-4">
            <p class="text-xs font-semibold text-amber-600 uppercase">Pending</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $pending }}</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-200 p-4">
            <p class="text-xs font-semibold text-blue-600 uppercase">In Progress</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $inProgress }}</p>
        </div>
        <div class="bg-white rounded-xl border border-emerald-200 p-4">
            <p class="text-xs font-semibold text-emerald-600 uppercase">Completed</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $completedWork }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-2 mb-1">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
            <h3 class="text-lg font-bold text-gray-900">Assigned Work</h3>
        </div>
        <p class="text-sm text-gray-500 mb-6">Your assigned orders to work on. Update status as you progress.</p>

        @if(count($myAssignments) === 0)
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <svg class="w-14 h-14 text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                </svg>
                <h3 class="text-base font-bold text-gray-400">No Work Assigned</h3>
                <p class="text-sm text-gray-400 mt-1">Your manager will assign work orders to you.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($myAssignments as $assignment)
                @php
                    $statusConfig = match($assignment['status']) {
                        'pending' => ['label' => 'Pending', 'color' => 'bg-amber-100 text-amber-700 border-amber-200', 'bg' => 'border-l-amber-400'],
                        'in_progress' => ['label' => 'In Progress', 'color' => 'bg-blue-100 text-blue-700 border-blue-200', 'bg' => 'border-l-blue-400'],
                        'completed' => ['label' => 'Completed', 'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'bg' => 'border-l-emerald-400'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => 'bg-gray-100 text-gray-500 border-gray-200', 'bg' => 'border-l-gray-400'],
                        default => ['label' => 'Unknown', 'color' => 'bg-gray-100 text-gray-500 border-gray-200', 'bg' => 'border-l-gray-400'],
                    };
                    $priorityConfig = match($assignment['priority']) {
                        'urgent' => ['label' => 'URGENT', 'color' => 'bg-red-50 text-red-600 border-red-200'],
                        'high' => ['label' => 'HIGH', 'color' => 'bg-orange-50 text-orange-600 border-orange-200'],
                        default => ['label' => 'NORMAL', 'color' => 'bg-gray-50 text-gray-500 border-gray-200'],
                    };
                @endphp
                <div class="border border-gray-200 rounded-xl p-2 sm:p-5 border-l-4 {{ $statusConfig['bg'] }} hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">Order {{ $assignment['order_id'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $assignment['customer'] }} &bull; Assigned {{ \Carbon\Carbon::parse($assignment['assigned_date'])->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center flex-col gap-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $priorityConfig['color'] }}">{{ $priorityConfig['label'] }}</span>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusConfig['color'] }}">{{ $statusConfig['label'] }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                </svg>
                                <span>{{ $assignment['items'] ?: 'No items listed' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                                <span>{{ $assignment['delivery_address'] ?: 'No address' }}</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/>
                                </svg>
                                <span>Deliver by <strong>{{ \Carbon\Carbon::parse($assignment['delivery_date'])->format('M d, Y') }}</strong></span>
                            </div>
                        </div>
                    </div>

                    @if($assignment['notes'])
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                        <p class="text-xs font-semibold text-amber-800 mb-0.5">Notes from Manager</p>
                        <p class="text-xs text-amber-700">{{ $assignment['notes'] }}</p>
                    </div>
                    @endif

                    {{-- Order Items Progress Section --}}
                    @if(!empty($assignment['order_items']) && $assignment['status'] !== 'completed' && $assignment['status'] !== 'cancelled')
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <h5 class="text-xs font-bold text-gray-700 uppercase mb-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/>
                            </svg>
                            Item Progress{{ $assignment['phase_number'] ? ' — Phase ' . $assignment['phase_number'] : '' }}
                        </h5>
                        <form onsubmit="submitProgress(event, {{ $assignment['id'] }})" id="progressForm-{{ $assignment['id'] }}">
                            <div class="space-y-3">
                                @foreach($assignment['order_items'] as $item)
                                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-100">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                                        <p class="text-xs text-gray-500">Required: {{ $item['quantity'] }} pcs</p>
                                        <p class="text-xs font-semibold {{ $item['completed_qty'] >= $item['quantity'] ? 'text-emerald-600' : 'text-blue-600' }}">Completed so far: {{ $item['completed_qty'] }} / {{ $item['quantity'] }} pcs</p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item['id'] }}">
                                        <div class="flex items-center flex-col gap-2">
                                            <span class="text-xs text-gray-400">+</span>
                                            <input type="number" name="items[{{ $loop->index }}][add_qty]" 
                                                   value="0" 
                                                   min="0" max="{{ $item['quantity'] - $item['completed_qty'] }}" 
                                                   placeholder="0"
                                                   class="w-20 px-2 py-1.5 border border-gray-300 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <span class="text-xs text-gray-500">pcs</span>
                                        </div>
                                        <span class="text-[10px] text-gray-400">{{ $item['quantity'] - $item['completed_qty'] }} remaining</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="flex items-center justify-between mt-4 flex-col sm:flex-row pt-3 border-t border-gray-200">
                                @php
                                    $totalQty = collect($assignment['order_items'])->sum('quantity');
                                    $totalCompleted = collect($assignment['order_items'])->sum('completed_qty');
                                    $progressPct = $totalQty > 0 ? round(($totalCompleted / $totalQty) * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-3">
                                    <div class="w-32 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $progressPct }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-600">{{ $totalCompleted }} / {{ $totalQty }} items &middot; {{ $progressPct }}% done</span>
                                </div>
                                <button type="submit" class="flex items-center my-2 gap-1.5 px-2 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
                                    </svg>
                                    Update Progress
                                </button>
                            </div>
                        </form>

                        {{-- Progress History --}}
                        @if(!empty($assignment['progress_history']))
                        <div class="mt-4 border border-gray-100 rounded-lg overflow-hidden">
                            <div class="bg-gray-100 px-3 py-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Progress History</p>
                            </div>
                            <div class="divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                @foreach($assignment['progress_history'] as $log)
                                <div class="flex items-center justify-between px-3 py-2 bg-white">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-[10px] font-bold text-emerald-700 shrink-0">
                                            {{ strtoupper(substr($log['employee'], 0, 1)) }}
                                        </div>
                                        <span class="text-xs font-semibold text-gray-800">{{ $log['employee'] }}</span>
                                        <span class="text-xs text-gray-400">added</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">+{{ $log['qty_added'] }} pcs</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 shrink-0 ml-2">{{ $log['time'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Show completed progress for completed assignments --}}
                    @if(!empty($assignment['order_items']) && $assignment['status'] === 'completed')
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                        <h5 class="text-xs font-bold text-emerald-700 uppercase mb-3">All Items Completed{{ $assignment['phase_number'] ? ' — Phase ' . $assignment['phase_number'] : '' }}</h5>
                        <div class="space-y-2">
                            @foreach($assignment['order_items'] as $item)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-emerald-800">{{ $item['name'] }}</span>
                                <span class="font-semibold text-emerald-700">{{ $item['completed_qty'] }} / {{ $item['quantity'] }}</span>
                            </div>
                            @endforeach
                        </div>

                        {{-- Progress History for completed --}}
                        @if(!empty($assignment['progress_history']))
                        <div class="mt-4 border border-emerald-100 rounded-lg overflow-hidden">
                            <div class="bg-emerald-100 px-3 py-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Progress History</p>
                            </div>
                            <div class="divide-y divide-emerald-50 max-h-48 overflow-y-auto">
                                @foreach($assignment['progress_history'] as $log)
                                <div class="flex items-center justify-between px-3 py-2 bg-white">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-[10px] font-bold text-emerald-700 shrink-0">
                                            {{ strtoupper(substr($log['employee'], 0, 1)) }}
                                        </div>
                                        <span class="text-xs font-semibold text-gray-800">{{ $log['employee'] }}</span>
                                        <span class="text-xs text-gray-400">added</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">+{{ $log['qty_added'] }} pcs</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 shrink-0 ml-2">{{ $log['time'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($assignment['status'] !== 'completed' && $assignment['status'] !== 'cancelled')
                    <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                        @if($assignment['status'] === 'pending')
                            <button onclick="updateMyAssignment({{ $assignment['id'] }}, 'in_progress', this)"
                                    class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                                </svg>
                                Start Working
                            </button>
                        @endif
                    </div>
                    @endif

                    @if($assignment['status'] === 'completed')
                    <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                        <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Completed &mdash; Ready for delivery
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

{{-- Toast Container --}}
<div id="toastContainer" class="fixed top-6 right-6 z-100 space-y-2"></div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function updateMyAssignment(assignmentId, newStatus, btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Updating...';

        fetch(`/assignments/${assignmentId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ status: newStatus }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const label = newStatus === 'in_progress' ? 'In Progress' : 'Completed';
                showToast(`Assignment updated to ${label}!`, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Failed to update assignment.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    function submitProgress(event, assignmentId) {
        event.preventDefault();
        const form = document.getElementById(`progressForm-${assignmentId}`);
        const formData = new FormData(form);
        
        // Build items array from form data
        const items = [];
        let index = 0;
        while (formData.has(`items[${index}][id]`)) {
            const addQty = parseInt(formData.get(`items[${index}][add_qty]`)) || 0;
            if (addQty > 0) {
                items.push({
                    id: parseInt(formData.get(`items[${index}][id]`)),
                    add_qty: addQty,
                });
            }
            index++;
        }

        if (items.length === 0) {
            showToast('Please enter at least 1 item quantity to add.', 'error');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Saving...';

        fetch('/assignments/update-progress', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                assignment_id: assignmentId,
                items: items,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.all_completed) {
                    showToast('All items completed! Order is now Ready for Delivery.', 'success');
                } else {
                    showToast('Progress updated successfully!', 'success');
                }
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast(data.message || 'Failed to update progress.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    function showToast(message, type) {
        const existing = document.getElementById('employeeToast');
        if (existing) existing.remove();

        const bgColor = type === 'success' ? 'bg-emerald-600' : 'bg-red-600';
        const toast = document.createElement('div');
        toast.id = 'employeeToast';
        toast.className = `${bgColor} text-white px-5 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2`;
        toast.innerHTML = `
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                ${type === 'success'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>'
                }
            </svg>
            ${message}
        `;
        document.getElementById('toastContainer').appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
</script>
@endpush
@endsection
