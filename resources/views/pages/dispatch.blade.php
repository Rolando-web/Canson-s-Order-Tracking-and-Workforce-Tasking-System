@extends('partials.app', ['title' => 'Dispatch - Canson', 'activePage' => 'dispatch'])

@push('styles')
    @vite('resources/css/pages/dispatch.css')
@endpush

@section('nav')
    <div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">Manager</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('l, F d, Y') }}</span>
            <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
        </div>
    </div>
@endsection

@section('content')
<div class="dispatch-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Dispatch & Delivery</h2>
            <p class="text-gray-500 mt-1">Orders ready for delivery. Click "Deliver" to mark as delivered.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex flex-col rounded-lg border border-gray-300 overflow-hidden sm:flex-row">
                <button class="dispatch-tab-btn p-2 text-[11px] sm:text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white" data-status="Ready for Delivery">Ready for Delivery</button>
                <button class="dispatch-tab-btn p-2 text-[11px] sm:text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50" data-status="Delivered">Delivered</button>
            </div>
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        {{-- Ready for Delivery --}}
        <div class="bg-white rounded-xl flex justify-center border border-gray-200 p-5">
           <div class="flex justify-center flex-col">
             <div class="w-8 h-8 md:w-10 rounded-xl flex-none bg-emerald-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                </svg>
            </div>
           </div>
        <div class="grow flex flex-col justify-center">
            <p class="text-sm font-medium text-emerald-600 text-end">Ready for Delivery</p>
            <p class="text-3xl font-bold text-gray-900 mt-1 text-end">{{ $readyCount ?? 0 }}</p>
        </div>
        </div>

        {{-- Delivered --}}
        <div class="bg-white rounded-xl flex justify-center border border-gray-200 p-5">
        <div class="flex justify-center flex-col">  
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div> 
            <div class="grow flex flex-col justify-center">
            <p class="text-sm font-medium text-gray-500 text-end">Delivered</p>
            <p class="text-3xl font-bold text-gray-900 mt-1 text-end">{{ $deliveredCount ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <div class="relative max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input id="dispatchSearch" type="text" placeholder="Search orders..." oninput="filterDispatch()" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        </div>
    </div>

    {{-- Order Cards --}}
    @if(count($orders) === 0)
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <svg class="w-14 h-14 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
        </svg>
        <h3 class="text-base font-bold text-gray-400">No Orders Ready for Delivery</h3>
        <p class="text-sm text-gray-400 mt-1">Orders will appear here when employees complete all items.</p>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[800px]">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Delivery Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($orders as $order)
                <tr class="dispatch-card hover:bg-gray-50 transition-colors cursor-pointer" data-status="{{ $order['status'] }}" data-search="{{ strtolower($order['customer'] . ' ' . $order['order_id'] . ' ' . $order['items']) }}" data-order-id="{{ $order['id'] }}" onclick="openDetailModal({{ $order['id'] }})">
                    <td class="px-5 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">{{ $order['order_id'] }}</span>
                        @if($order['has_phases'] && $order['current_phase'])
                        <div class="mt-1">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.689c0-.864.933-1.406 1.683-.977l7.108 4.061a1.125 1.125 0 010 1.954l-7.108 4.061A1.125 1.125 0 013 16.811V8.69zM12.75 8.689c0-.864.933-1.406 1.683-.977l7.108 4.061a1.125 1.125 0 010 1.954l-7.108 4.061a1.125 1.125 0 01-1.683-.977V8.69z"/></svg>
                                Phase {{ $order['current_phase']['phase_number'] }}
                            </span>
                        </div>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-sm font-semibold text-gray-900">{{ $order['customer'] }}</p>
                        <p class="text-xs text-gray-400">{{ $order['contact'] }}</p>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-600 max-w-[200px]" title="{{ $order['items'] }}">
                        <div>{{ Str::limit($order['items'], 35) }}</div>
                        @if($order['has_cover_items'] ?? false)
                        <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            INCLUDES COVER ITEMS
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-600">{{ date('M d, Y', strtotime($order['delivery_date'])) }}</td>
                    <td class="px-5 py-3 text-sm font-bold text-emerald-600">₱{{ number_format((float)$order['total_amount'], 2) }}</td>
                    <td class="px-5 py-3">
                        @php
                            $statusConfig = match($order['status']) {
                                'Ready for Delivery' => 'bg-amber-50 text-amber-600 border-amber-200',
                                'Delivered' => 'bg-green-50 text-green-600 border-green-200',
                                default => 'bg-gray-50 text-gray-500 border-gray-200',
                            };
                        @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusConfig }}">{{ $order['status'] }}</span>
                    </td>
                    <td class="px-5 py-3">
                        @if($order['status'] === 'Ready for Delivery')
                        <button onclick="event.stopPropagation();deliverOrder({{ $order['id'] }}, this)" class="deliver-btn flex items-center gap-1.5 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Deliver
                        </button>
                        @else
                        <span class="text-xs text-gray-400">&mdash;</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif
</div>

{{-- ============================================ --}}
{{-- DAMAGE REPORT MODAL (shown after delivery)  --}}
{{-- ============================================ --}}
<div id="damageModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4" style="display:none;" onclick="if(event.target===this)closeDamageModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    Delivery Damage Report
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Order <span id="dmgOrderRef" class="font-medium text-gray-600"></span></p>
            </div>
            <button type="button" onclick="closeDamageModal()" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Info Message --}}
        <div class="px-6 pt-5">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-2">
                <svg class="w-5 h-5 text-blue-500 flex-none mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                <p class="text-xs text-blue-700">Were any items damaged during delivery? Check the items below and enter the damage details. Damaged items will be <strong>deducted from stock</strong> and a <strong>replacement will be included</strong> in the customer's next order.</p>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="px-6 py-4">
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Item</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 w-20">Ordered</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 w-24">Damaged Qty</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Damage Reason</th>
                        </tr>
                    </thead>
                    <tbody id="dmgItemsBody" class="divide-y divide-gray-100">
                        {{-- Populated via JS --}}
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-between items-center px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            <button type="button" id="dmgSkipBtn" onclick="skipDamageReport()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                No Damages — Just Deliver
            </button>
            <button type="button" id="dmgSubmitBtn" onclick="submitDamageReport()" class="px-5 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                Report Damages & Deliver
            </button>
        </div>
    </div>
</div>

<script>
window.allOrders = @json($orders);
</script>

{{-- ============================================ --}}
{{-- ORDER DETAIL MODALS (Blade-rendered per order) --}}
{{-- ============================================ --}}
@foreach($orders as $order)
@php
    $detailStatusConfig = match($order['status']) {
        'Ready for Delivery' => 'bg-amber-50 text-amber-600 border-amber-200',
        'Delivered' => 'bg-green-50 text-green-600 border-green-200',
        default => 'bg-gray-50 text-gray-500 border-gray-200',
    };
@endphp
<div id="dispatchDetailModal-{{ $order['id'] }}" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4" style="display:none;" onclick="if(event.target===this)closeDetailModal({{ $order['id'] }})">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="sticky top-0 bg-emerald-600 rounded-t-2xl px-6 py-4 flex items-center justify-between z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Order Details</h3>
                    <p class="text-emerald-100 text-xs">{{ $order['order_id'] }}</p>
                </div>
            </div>
            <button onclick="closeDetailModal({{ $order['id'] }})" class="text-white/70 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-5">
            {{-- Customer Info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Customer</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $order['customer'] }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Contact</p>
                    <p class="text-sm text-gray-700">{{ $order['contact'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Delivery Address</p>
                    <p class="text-sm text-gray-700">{{ $order['address'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Delivery Date</p>
                    <p class="text-sm text-gray-700">{{ date('M d, Y', strtotime($order['delivery_date'])) }}</p>
                </div>
            </div>

            {{-- Status & Amount --}}
            <div class="flex flex-wrap items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                <div class="flex-1 min-w-[120px]">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Status</p>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border {{ $detailStatusConfig }}">{{ $order['status'] }}</span>
                </div>
                <div class="flex-1 min-w-[120px]">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Amount</p>
                    <p class="text-lg font-bold text-emerald-600">₱{{ number_format((float)$order['total_amount'], 2) }}</p>
                </div>
            </div>

            {{-- Items Table --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Order Items</p>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[400px]">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Item</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 w-20">Qty</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 w-28">Price</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 w-28">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($order['item_details'] as $item)
                            <tr>
                                <td class="px-4 py-2.5 text-sm text-gray-900">
                                    {{ $item['name'] }}
                                    @if($item['is_cover'] ?? false)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 ml-1">COVER</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-sm text-gray-600 text-center">{{ $item['quantity'] }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-600 text-right">₱{{ number_format((float)$item['unit_price'], 2) }}</td>
                                <td class="px-4 py-2.5 text-sm font-semibold text-gray-900 text-right">₱{{ number_format($item['quantity'] * (float)$item['unit_price'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            {{-- Phases Section --}}
            @if($order['has_phases'] && count($order['phases']) > 0)
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Delivery Phases</p>
                <div class="space-y-2">
                    @foreach($order['phases'] as $phase)
                    @php
                        $phaseStatusClass = $phase['status'] === 'Delivered'
                            ? 'bg-green-50 text-green-600 border-green-200'
                            : 'bg-indigo-50 text-indigo-600 border-indigo-200';
                    @endphp
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-white">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-indigo-700 uppercase">Phase {{ $phase['phase_number'] }}</span>
                            <span class="text-xs text-gray-500">{{ $phase['delivery_date'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(($phase['damage_qty'] ?? 0) > 0)
                            <span class="text-[10px] font-medium text-red-600 bg-red-50 border border-red-200 px-1.5 py-0.5 rounded">{{ $phase['damage_qty'] }} damaged</span>
                            @endif
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium border {{ $phaseStatusClass }}">{{ $phase['status'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Delivered At --}}
            @if($order['status'] === 'Delivered')
            <div class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-xs text-green-700">Delivered on <strong>{{ $order['delivered_at'] }}</strong></p>
            </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="flex justify-end px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            <button type="button" onclick="closeDetailModal({{ $order['id'] }})" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
@vite('resources/js/pages/dispatch.js')
@endpush
