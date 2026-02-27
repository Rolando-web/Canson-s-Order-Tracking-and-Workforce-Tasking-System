@extends('partials.app', ['title' => 'Dispatch - Canson', 'activePage' => 'dispatch'])

@push('styles')
    @vite('resources/css/pages/dispatch.css')
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
<div class="dispatch-page">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Dispatch & Delivery</h2>
        <p class="text-gray-500 mt-1">Orders ready for delivery. Click "Deliver" to mark as delivered.</p>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        {{-- Ready for Delivery --}}
        <div class="bg-white rounded-xl flex justify-center border-2 border-emerald-500 p-5">
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

    {{-- Search & Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input id="dispatchSearch" type="text" placeholder="Search orders..." oninput="filterDispatch()" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <select id="dispatchStatusFilter" onchange="filterDispatch()" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">All Status</option>
                <option value="Ready for Delivery">Ready for Delivery</option>
                <option value="Delivered">Delivered</option>
            </select>
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
    <div class="space-y-4">
        @foreach($orders as $order)
        <div class="dispatch-card bg-white rounded-xl {{ $order['status'] === 'Delivered' ? 'border-2 border-green-400' : 'border-2 border-emerald-300' }} p-6" 
             data-status="{{ $order['status'] }}" 
             data-search="{{ strtolower($order['customer'] . ' ' . $order['order_id'] . ' ' . $order['items']) }}">
            <div class="flex flex-col gap-4">
                {{-- Header with Status and Actions --}}
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">{{ $order['order_id'] }}</span>
                        @php
                            $statusConfig = match($order['status']) {
                                'Ready for Delivery' => ['label' => 'Ready for Delivery', 'color' => 'bg-amber-50 text-amber-600 border-amber-200'],
                                'Delivered' => ['label' => 'Delivered', 'color' => 'bg-green-50 text-green-600 border-green-200'],
                                default => ['label' => 'Unknown', 'color' => 'bg-gray-50 text-gray-500 border-gray-200'],
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusConfig['color'] }}">{{ $statusConfig['label'] }}</span>
                    </div>
                    <div class="flex gap-2">
                        @if($order['status'] === 'Ready for Delivery')
                        <button onclick="deliverOrder({{ $order['id'] }}, this)" class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Deliver
                        </button>
                        @else
                        <span class="flex items-center gap-2 px-3 py-2 border border-green-200 rounded-lg text-xs font-medium text-green-600 bg-green-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Delivered
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Customer and Order Info --}}
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $order['customer'] }}</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $order['items'] }}</p>
                    <p class="flex items-center gap-1 text-sm text-gray-400 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        {{ $order['address'] }}
                    </p>
                </div>

                {{-- Details --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Contact</label>
                        <p class="text-sm text-gray-900">{{ $order['contact'] }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Delivery Date</label>
                        <div class="flex items-center gap-1 text-sm text-gray-900">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                            {{ date('M d, Y', strtotime($order['delivery_date'])) }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Amount</label>
                        <p class="text-sm font-bold text-emerald-600">₱{{ number_format((float)$order['total_amount'], 2) }}</p>
                    </div>
                </div>

                {{-- Worker Info --}}
                @if($order['assigned'])
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Assigned to: <span class="font-medium text-gray-700">{{ $order['assigned'] }}</span></p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function deliverOrder(orderId, btn) {
    if (!confirm('Mark this order as delivered?')) return;
    
    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Delivering...';
    
    fetch('/dispatch/deliver', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ order_id: orderId }),
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to deliver order.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('An error occurred.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function filterDispatch() {
    var search = document.getElementById('dispatchSearch').value.toLowerCase();
    var statusFilter = document.getElementById('dispatchStatusFilter').value;

    document.querySelectorAll('.dispatch-card').forEach(function(card) {
        var matchSearch = !search || card.getAttribute('data-search').includes(search);
        var matchStatus = !statusFilter || card.getAttribute('data-status') === statusFilter;
        card.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}
</script>

@endsection
