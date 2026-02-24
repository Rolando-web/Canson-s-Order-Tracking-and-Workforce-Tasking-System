@extends('partials.app', ['title' => 'My Assignments - Canson', 'activePage' => 'assignments'])

@push('styles')
    @vite('resources/css/pages/assignments.css')
@endpush

@section('nav')
    <div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">My Work</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('l, F d, Y') }}</span>
            <div class="flex items-center gap-2">
                @php
                    $deptBadge = $department === 'Driver'
                        ? ['label' => 'Driver', 'color' => 'bg-orange-100 text-orange-700']
                        : ['label' => 'Worker', 'color' => 'bg-emerald-100 text-emerald-700'];
                @endphp
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $deptBadge['color'] }}">{{ $deptBadge['label'] }}</span>
                <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">
                    {{ auth()->user()->initial }}
                </div>
            </div>
        </div>
    </div>
@endsection

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

    {{-- ============================================== --}}
    {{-- ASSIGNED WORK SECTION (Both Workers & Drivers) --}}
    {{-- ============================================== --}}
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
                <div class="border border-gray-200 rounded-xl p-5 border-l-4 {{ $statusConfig['bg'] }} hover:shadow-md transition-shadow">
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
                        <div class="flex items-center gap-2">
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
                        @elseif($assignment['status'] === 'in_progress')
                            <button onclick="updateMyAssignment({{ $assignment['id'] }}, 'completed', this)"
                                    class="flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                Mark as Completed
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

    {{-- ============================================== --}}
    {{-- ASSIGNED DELIVERIES SECTION (Drivers Only)     --}}
    {{-- ============================================== --}}
    @if($department === 'Driver')
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center gap-2 mb-1">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
            </svg>
            <h3 class="text-lg font-bold text-gray-900">Assigned Deliveries</h3>
        </div>
        <p class="text-sm text-gray-500 mb-6">Deliveries assigned to you. Update delivery status as you go.</p>

        @if(count($myDeliveries) === 0)
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <svg class="w-14 h-14 text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
                <h3 class="text-base font-bold text-gray-400">No Deliveries Assigned</h3>
                <p class="text-sm text-gray-400 mt-1">Your manager will assign deliveries to you when orders are ready.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($myDeliveries as $delivery)
                @php
                    $dStatusConfig = match($delivery['status']) {
                        'pending' => ['label' => 'Pending Pickup', 'color' => 'bg-amber-100 text-amber-700 border-amber-200', 'bg' => 'border-l-amber-400'],
                        'in_transit' => ['label' => 'In Transit', 'color' => 'bg-blue-100 text-blue-700 border-blue-200', 'bg' => 'border-l-blue-400'],
                        'delivered' => ['label' => 'Delivered', 'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'bg' => 'border-l-emerald-400'],
                        default => ['label' => 'Unknown', 'color' => 'bg-gray-100 text-gray-500 border-gray-200', 'bg' => 'border-l-gray-400'],
                    };
                @endphp
                <div class="border border-gray-200 rounded-xl p-5 border-l-4 {{ $dStatusConfig['bg'] }} hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">Delivery &mdash; Order {{ $delivery['order_id'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $delivery['customer'] }} &bull; Deliver by {{ \Carbon\Carbon::parse($delivery['delivery_date'])->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $dStatusConfig['color'] }}">{{ $dStatusConfig['label'] }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                </svg>
                                <span>{{ $delivery['items'] ?: 'No items listed' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                                <span>{{ $delivery['delivery_address'] ?: 'No address' }}</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6"/>
                                </svg>
                                <span>Vehicle: <strong>{{ $delivery['vehicle'] }}</strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    @if($delivery['status'] === 'pending')
                    <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                        <button onclick="updateMyDelivery({{ $delivery['id'] }}, 'ship', this)"
                                class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193"/>
                            </svg>
                            Start Delivery
                        </button>
                    </div>
                    @elseif($delivery['status'] === 'in_transit')
                    <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                        <button onclick="updateMyDelivery({{ $delivery['id'] }}, 'deliver', this)"
                                class="flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                            Mark as Delivered
                        </button>
                        <p class="text-xs text-purple-500 ml-2">
                            <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                            </svg>
                            You cannot be assigned new work while on delivery.
                        </p>
                    </div>
                    @elseif($delivery['status'] === 'delivered')
                    <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                        <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Delivered successfully
                            @if($delivery['delivery_time'])
                                &bull; {{ \Carbon\Carbon::parse($delivery['delivery_time'])->format('M d, Y h:i A') }}
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
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

    function updateMyDelivery(dispatchId, action, btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Updating...';

        fetch('/assignments/delivery-status', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ dispatch_id: dispatchId, action: action }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const label = action === 'ship' ? 'In Transit' : 'Delivered';
                showToast(`Delivery updated to ${label}!`, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Failed to update delivery.', 'error');
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
