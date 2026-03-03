@extends('partials.app', ['title' => 'Orders - Canson', 'activePage' => 'orders'])

@push('styles')
    @vite('resources/css/pages/orders.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/orders.js')
@endpush

@section('nav')
    <div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">Manager</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('l, F d, Y') }}</span>
            <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">AD</div>
        </div>
    </div>
@endsection

@section('content')
<div class="orders-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Orders</h2>
            <p class="text-gray-500 mt-1">Manage and track customer orders</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openAddOrderModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Order
            </button>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
        <div class="flex items-center gap-4">
            <div class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input id="orderSearch" type="text" placeholder="Search orders, customers..." oninput="filterOrders()" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                <select id="orderStatusFilter" onchange="filterOrders()" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="In-Progress">In-Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[900px]">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Name</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Delivery Date</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phases</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($orders as $order)
                <tr class="hover:bg-gray-50 transition-colors order-row" data-status="{{ $order['status'] }}" data-search="{{ strtolower($order['id'] . ' ' . $order['items'] . ' ' . $order['customer'] . ' ' . $order['contact']) }}">
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $order['id'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700 max-w-[200px] truncate" title="{{ $order['items'] }}">{{ Str::limit($order['items'], 30) }}</td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $order['customer'] }}</p>
                            <p class="text-xs text-gray-500">{{ $order['contact'] }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                            {{ date('M d, Y', strtotime($order['delivery_date'])) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ number_format($order['total_qty']) }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">₱{{ number_format($order['total'], 2) }}</td>
                    <td class="px-6 py-4">
                        @php
                            $statusColor = match($order['status']) {
                                'Completed' => 'bg-green-50 text-green-600 border-green-200',
                                'In-Progress' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                'Pending' => 'bg-gray-50 text-gray-500 border-gray-200',
                                default => 'bg-gray-50 text-gray-500 border-gray-200',
                            };
                        @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColor }}">{{ $order['status'] }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border {{ $order['priorityColor'] }}">{{ $order['priority'] }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($order['phase_count'] > 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600 border border-indigo-200">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25"/></svg>
                                {{ $order['phase_count'] }} {{ Str::plural('phase', $order['phase_count']) }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="showOrderDetails('{{ $order['id'] }}')" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            View Details
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    @include('pages.Modals.order-details-modal')

    {{-- Add Order Modal --}}
    <div id="addOrderModal" class="fixed inset-0 z-50 hidden">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAddOrderModal()"></div>

        {{-- Modal Content --}}
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative" onclick="event.stopPropagation()">
                {{-- Header --}}
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">New Order</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Fill in the details to create a new order</p>
                    </div>
                    <button onclick="closeAddOrderModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form id="addOrderForm" onsubmit="submitAddOrder(event)">
                    <div class="px-6 py-5 space-y-6">
                        {{-- Customer Information --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                Customer Information
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="relative">
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Customer Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="customer_name" id="orderCustomerName" required placeholder="e.g. St. Mary School" autocomplete="off" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" oninput="onCustomerNameInput(this.value)" onblur="setTimeout(function(){ hideCustomerDropdown(); }, 200)">
                                    {{-- Autocomplete dropdown --}}
                                    <div id="customerSuggestionsDropdown" class="hidden absolute z-20 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Contact Number <span class="text-red-500">*</span></label>
                                    <input type="text" name="contact_number" required placeholder="e.g. 0917-123-4567" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                </div>
                            </div>

                            {{-- Pending Damage Claims Alert --}}
                            <div id="damageClaimsAlert" class="mt-3 hidden">
                                <div class="bg-amber-50 border border-amber-300 rounded-lg p-3">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-amber-600 flex-none mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-amber-800">This customer has pending damage claims!</p>
                                            <p class="text-xs text-amber-600 mt-0.5">Replacement items have been automatically added to this order (₱0.00 — FREE cover):</p>
                                            <div id="damageClaimsList" class="mt-2 space-y-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Delivery Address <span class="text-red-500">*</span></label>
                                <input type="text" name="delivery_address" required placeholder="e.g. 123 School Lane, Quezon City" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>
                        </div>

                        {{-- Order Details --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                Order Details
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Delivery Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="delivery_date" id="deliveryDateInput" required min="{{ date('Y-m-d') }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Priority <span class="text-red-500">*</span></label>
                                    <select name="priority" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                        <option value="Normal">Normal</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Order Items --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                    Order Items
                                </h4>
                                <button type="button" onclick="addOrderItem()" class="text-emerald-600 hover:text-emerald-700 text-xs font-medium flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-emerald-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Add Item
                                </button>
                            </div>
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="overflow-x-auto">
                                <table class="w-full min-w-[600px]">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Item Name</th>
                                            <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 w-28">Qty</th>
                                            <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 w-32">Unit Price (₱)</th>
                                            <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 w-32">Subtotal</th>
                                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 w-12"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="addOrderItemsBody" class="divide-y divide-gray-100">
                                        {{-- Default first row --}}
                                        <tr class="order-item-row">
                                            <td class="px-4 py-2">
                                                <select name="items[0][name]" required onchange="onItemSelected(this)" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                                    <option value="">-- Select item --</option>
                                                    @foreach($inventoryItems as $item)
                                                    <option value="{{ $item->name }}" data-price="{{ $item->unit_price }}" data-stock="{{ $item->stock }}">{{ $item->name }} ({{ $item->stock }} in stock)</option>
                                                    @endforeach
                                                </select>
                                                <div class="stock-indicator mt-1 text-xs"></div>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" name="items[0][qty]" required min="1" value="1" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-emerald-500 item-qty" oninput="recalcOrderTotal()">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" name="items[0][price]" required min="0" step="0.01" value="0" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-emerald-500 item-price" oninput="recalcOrderTotal()">
                                            </td>
                                            <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900 item-subtotal">₱0.00</td>
                                            <td class="px-4 py-2 text-center">
                                                <button type="button" onclick="removeOrderItem(this)" class="text-gray-300 hover:text-red-500 transition-colors" title="Remove item">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-right text-sm font-bold text-gray-900">Total Amount:</td>
                                            <td class="px-4 py-3 text-right text-base font-bold text-emerald-600" id="addOrderTotal">₱0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                            </div>
                        </div>

                        {{-- Phases --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                                    Delivery Phases
                                    <span class="text-xs text-gray-400 font-normal">(optional — split order into batches)</span>
                                </h4>
                                <button type="button" onclick="addPhase()" class="text-indigo-600 hover:text-indigo-700 text-xs font-medium flex items-center gap-1 px-3 py-1.5 rounded-lg hover:bg-indigo-50 border border-indigo-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Add Phase
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mb-3">Phases auto-fill from Order Items above. Just set how many to deliver per phase.</p>
                            {{-- Remaining qty hint bar --}}
                            <div id="phaseQtyWarning" class="hidden mb-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2"></div>
                            <div id="phasesContainer" class="space-y-3">
                                {{-- Phase cards added dynamically --}}
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes</label>
                            <textarea name="notes" rows="3" placeholder="Any special instructions or notes..." class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
                        <button type="button" onclick="closeAddOrderModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.inventoryItems = @json($inventoryItems ?? []);
window.ordersData = @json(collect($orders)->keyBy('id'));
window.pendingCoverClaimIds = [];

function filterOrders() {
    var search = document.getElementById('orderSearch').value.toLowerCase();
    var status = document.getElementById('orderStatusFilter').value;
    document.querySelectorAll('.order-row').forEach(function(row) {
        var matchSearch = !search || row.getAttribute('data-search').includes(search);
        var matchStatus = !status || row.getAttribute('data-status') === status;
        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}

// ========== Customer Autocomplete ==========
var customerSearchTimeout = null;

function onCustomerNameInput(value) {
    clearTimeout(customerSearchTimeout);
    var dropdown = document.getElementById('customerSuggestionsDropdown');

    if (!value || value.trim().length < 2) {
        dropdown.classList.add('hidden');
        dropdown.innerHTML = '';
        return;
    }

    customerSearchTimeout = setTimeout(function() {
        fetch('/orders/customer-suggestions?q=' + encodeURIComponent(value.trim()), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(customers) {
            if (customers.length === 0) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                return;
            }

            dropdown.innerHTML = customers.map(function(c) {
                return '<button type="button" onclick="selectCustomer(\'' + c.name.replace(/'/g, "\\'") + '\', \'' + (c.contact || '').replace(/'/g, "\\'") + '\', \'' + (c.address || '').replace(/'/g, "\\'") + '\')" ' +
                    'class="w-full text-left px-3 py-2 hover:bg-emerald-50 flex items-center gap-2 transition-colors border-b border-gray-100 last:border-0">' +
                    '<div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-xs font-bold flex-none">' + c.name.charAt(0).toUpperCase() + '</div>' +
                    '<div class="min-w-0">' +
                    '<p class="text-sm font-medium text-gray-900 truncate">' + c.name + '</p>' +
                    '<p class="text-xs text-gray-400 truncate">' + (c.contact || '') + ' · ' + (c.address || '').substring(0, 30) + '</p>' +
                    '</div>' +
                    '</button>';
            }).join('');
            dropdown.classList.remove('hidden');
        })
        .catch(function() { dropdown.classList.add('hidden'); });
    }, 250);
}

window.selectCustomer = function(name, contact, address) {
    document.getElementById('orderCustomerName').value = name;
    document.getElementById('customerSuggestionsDropdown').classList.add('hidden');

    // Auto-fill contact and address
    var contactInput = document.querySelector('input[name="contact_number"]');
    var addressInput = document.querySelector('input[name="delivery_address"]');
    if (contactInput && contact) contactInput.value = contact;
    if (addressInput && address) addressInput.value = address;

    // Check for pending damage claims
    checkDamageClaims(name);
};

function hideCustomerDropdown() {
    document.getElementById('customerSuggestionsDropdown').classList.add('hidden');
}

// ========== Damage Claims Lookup + Auto-Add Cover Items ==========
var damageCheckTimeout = null;
function checkDamageClaims(name) {
    clearTimeout(damageCheckTimeout);
    var alert = document.getElementById('damageClaimsAlert');
    var list = document.getElementById('damageClaimsList');

    // Clean up any existing cover rows
    removeCoverRows();
    window.pendingCoverClaimIds = [];

    if (!name || name.trim().length < 2) {
        alert.classList.add('hidden');
        return;
    }

    damageCheckTimeout = setTimeout(function() {
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('/returns/pending-for-customer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ customer_name: name.trim() }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.claims && data.claims.length > 0) {
                // Show alert
                list.innerHTML = data.claims.map(function(c) {
                    return '<div class="flex items-center justify-between bg-white rounded px-2.5 py-1.5 border border-amber-200">' +
                        '<div class="flex items-center gap-2">' +
                        '<span class="text-xs font-medium text-amber-700">' + c.return_id + '</span>' +
                        '<span class="text-xs text-gray-700 font-semibold">' + c.item_name + '</span>' +
                        '<span class="text-xs text-gray-500">x' + c.quantity + '</span>' +
                        '</div>' +
                        '<span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">AUTO-ADDED</span>' +
                        '</div>';
                }).join('');
                alert.classList.remove('hidden');

                // Auto-add cover items to order table
                data.claims.forEach(function(c) {
                    addCoverItemRow(c);
                    window.pendingCoverClaimIds.push(c.id);
                });

                recalcOrderTotal();
            } else {
                alert.classList.add('hidden');
            }
        })
        .catch(function() { alert.classList.add('hidden'); });
    }, 300);
}

// Add a cover item row (non-removable, price=0, with COVER badge)
function addCoverItemRow(claim) {
    var tbody = document.getElementById('addOrderItemsBody');
    var items = window.inventoryItems || [];

    // Find the inventory item to get its name for the select
    var matchedItem = null;
    for (var i = 0; i < items.length; i++) {
        if (items[i].id === claim.item_id) {
            matchedItem = items[i];
            break;
        }
    }

    if (!matchedItem) return;

    var idx = tbody.querySelectorAll('.order-item-row').length;

    var row = '<tr class="order-item-row cover-item-row bg-amber-50/50" data-claim-id="' + claim.id + '">' +
        '<td class="px-4 py-2">' +
            '<div class="flex items-center gap-2">' +
                '<select name="items[' + idx + '][name]" required onchange="onItemSelected(this)" ' +
                    'class="w-full px-3 py-2.5 border border-amber-300 rounded-lg text-sm bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-400" disabled>' +
                    '<option value="' + matchedItem.name + '" selected data-price="0" data-stock="' + matchedItem.stock + '">' + matchedItem.name + '</option>' +
                '</select>' +
            '</div>' +
            '<div class="mt-1 flex items-center gap-1.5">' +
                '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-800">' +
                    '<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>' +
                    'DAMAGE COVER — ' + claim.return_id +
                '</span>' +
            '</div>' +
            '<input type="hidden" name="items[' + idx + '][name]" value="' + matchedItem.name + '">' +
        '</td>' +
        '<td class="px-4 py-2">' +
            '<input type="number" name="items[' + idx + '][qty]" required min="1" value="' + claim.quantity + '" ' +
                'class="w-full px-2 py-1.5 border border-amber-300 rounded text-sm text-right bg-amber-50 focus:outline-none item-qty" readonly>' +
        '</td>' +
        '<td class="px-4 py-2">' +
            '<input type="number" name="items[' + idx + '][price]" required min="0" step="0.01" value="0" ' +
                'class="w-full px-2 py-1.5 border border-amber-300 rounded text-sm text-right bg-amber-50 focus:outline-none item-price" readonly>' +
        '</td>' +
        '<td class="px-4 py-2 text-right text-sm font-semibold text-amber-700 item-subtotal">FREE</td>' +
        '<td class="px-4 py-2 text-center">' +
            '<span class="text-amber-400" title="Auto-added damage cover item">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>' +
            '</span>' +
        '</td>' +
    '</tr>';

    tbody.insertAdjacentHTML('beforeend', row);
}

function removeCoverRows() {
    document.querySelectorAll('.cover-item-row').forEach(function(row) {
        row.remove();
    });
}

// ========== Phases Builder ==========
var phaseCount = 0;

// Read current order items from the table
function getOrderItems() {
    var rows = document.querySelectorAll('#addOrderItemsBody .order-item-row:not(.cover-item-row)');
    var items = [];
    rows.forEach(function(row) {
        var nameEl = row.querySelector('select[name*="[name]"]') || row.querySelector('input[name*="[name]"]');
        var qtyEl  = row.querySelector('input[name*="[qty]"]');
        if (!nameEl || !qtyEl) return;
        var name = nameEl.value ? nameEl.value.trim() : '';
        var qty  = parseInt(qtyEl.value) || 0;
        if (name && qty > 0) items.push({ name: name, qty: qty });
    });
    return items;
}

// Sum qty allocated in ALL phases for a given item name
function getAllocatedQty(itemName) {
    var total = 0;
    document.querySelectorAll('.phase-card').forEach(function(card) {
        card.querySelectorAll('.phase-item-row').forEach(function(row) {
            var n = row.dataset.itemName;
            var q = parseInt(row.querySelector('.phase-qty-input').value) || 0;
            if (n === itemName) total += q;
        });
    });
    return total;
}

function updatePhaseWarnings() {
    var orderItems = getOrderItems();
    var warnings = [];
    orderItems.forEach(function(oi) {
        var allocated = getAllocatedQty(oi.name);
        if (allocated > oi.qty) {
            warnings.push('<strong>' + oi.name + '</strong>: allocated ' + allocated + ' but order only has ' + oi.qty);
        }
    });
    var warnDiv = document.getElementById('phaseQtyWarning');
    if (warnings.length) {
        warnDiv.innerHTML = '⚠ Over-allocated: ' + warnings.join('; ');
        warnDiv.classList.remove('hidden');
    } else {
        warnDiv.classList.add('hidden');
    }
    // Also refresh remaining hints on all phase item rows
    document.querySelectorAll('.phase-item-row').forEach(function(row) {
        var itemName = row.dataset.itemName;
        var orderQty = 0;
        orderItems.forEach(function(oi) { if (oi.name === itemName) orderQty = oi.qty; });
        var allocated = getAllocatedQty(itemName);
        var hint = row.querySelector('.phase-qty-hint');
        if (hint) {
            var remaining = orderQty - allocated;
            hint.textContent = allocated + ' of ' + orderQty + ' allocated' + (remaining < 0 ? ' (over by ' + Math.abs(remaining) + ')' : '');
            hint.className = 'phase-qty-hint text-xs mt-0.5 ' + (remaining < 0 ? 'text-red-500 font-semibold' : 'text-gray-400');
        }
    });
}

function addPhase() {
    var orderItems = getOrderItems();
    if (orderItems.length === 0) {
        alert('Please add Order Items first before adding a phase.');
        return;
    }

    phaseCount++;
    var pi = document.querySelectorAll('.phase-card').length; // zero-based index
    var container = document.getElementById('phasesContainer');

    var rowsHtml = '';
    orderItems.forEach(function(item, ii) {
        // Remaining after already-allocated phases
        var allocated = getAllocatedQty(item.name);
        var remaining = Math.max(0, item.qty - allocated);
        rowsHtml +=
            '<tr class="phase-item-row" data-item-name="' + item.name.replace(/"/g, '&quot;') + '">' +
                '<td class="px-3 py-2">' +
                    '<div class="text-sm font-medium text-gray-800">' + item.name + '</div>' +
                    '<input type="hidden" name="phases[' + pi + '][items][' + ii + '][name]" value="' + item.name.replace(/"/g, '&quot;') + '">' +
                '</td>' +
                '<td class="px-3 py-2 w-32">' +
                    '<input type="number" name="phases[' + pi + '][items][' + ii + '][qty]" ' +
                        'value="' + remaining + '" min="0" max="' + item.qty + '" ' +
                        'class="phase-qty-input w-full px-2 py-1.5 border border-gray-200 rounded-lg text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-400" ' +
                        'oninput="updatePhaseWarnings()">' +
                    '<div class="phase-qty-hint text-xs mt-0.5 text-gray-400"></div>' +
                '</td>' +
            '</tr>';
    });

    var card = document.createElement('div');
    card.className = 'phase-card border border-indigo-200 rounded-xl bg-white overflow-hidden shadow-sm';
    card.dataset.phaseIndex = pi;
    card.innerHTML =
        '<div class="flex items-center justify-between px-4 py-2.5 bg-indigo-50 border-b border-indigo-200">' +
            '<div class="flex items-center gap-3">' +
                '<span class="phase-label text-xs font-bold text-indigo-700 uppercase tracking-wide">Phase ' + phaseCount + '</span>' +
                '<div class="flex items-center gap-1.5">' +
                    '<label class="text-[11px] text-indigo-500">Delivery Date</label>' +
                    '<input type="date" name="phases[' + pi + '][delivery_date]" required ' +
                        'class="px-2 py-1 border border-indigo-300 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">' +
                '</div>' +
            '</div>' +
            '<button type="button" onclick="removePhase(this)" class="text-indigo-300 hover:text-red-500 transition-colors ml-2" title="Remove phase">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
            '</button>' +
        '</div>' +
        '<div class="p-3">' +
            '<table class="w-full">' +
                '<thead>' +
                    '<tr class="border-b border-gray-100">' +
                        '<th class="px-3 py-1.5 text-left text-xs font-semibold text-gray-500">Item</th>' +
                        '<th class="px-3 py-1.5 text-right text-xs font-semibold text-gray-500 w-32">Qty to Deliver</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody class="divide-y divide-gray-50">' + rowsHtml + '</tbody>' +
            '</table>' +
        '</div>';

    container.appendChild(card);
    updatePhaseWarnings();
}

function removePhase(btn) {
    btn.closest('.phase-card').remove();
    renumberPhases();
    updatePhaseWarnings();
}

function renumberPhases() {
    phaseCount = 0;
    document.querySelectorAll('.phase-card').forEach(function(card, ci) {
        phaseCount++;
        card.dataset.phaseIndex = ci;
        var label = card.querySelector('.phase-label');
        if (label) label.textContent = 'Phase ' + phaseCount;
        card.querySelectorAll('[name]').forEach(function(el) {
            el.name = el.name.replace(/phases\[\d+\]/, 'phases[' + ci + ']');
        });
    });
}

// Reset phases when modal closes
function resetPhases() {
    phaseCount = 0;
    document.getElementById('phasesContainer').innerHTML = '';
    document.getElementById('phaseQtyWarning').classList.add('hidden');
}
</script>
@endsection
