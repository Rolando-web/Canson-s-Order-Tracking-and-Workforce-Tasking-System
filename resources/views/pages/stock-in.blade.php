@extends('partials.app', ['title' => 'Stock In - Canson', 'activePage' => 'stock-in'])

@push('styles')
    @vite('resources/css/pages/inventory.css')
@endpush

@section('nav')
    <div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">Manager</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('l, F d, Y') }}</span>
            <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">
                {{ auth()->user()->initial ?? 'AD' }}
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="inventory-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Stock In</h2>
            <p class="text-gray-500 mt-1">Add incoming stock to inventory</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex flex-col rounded-lg border border-gray-300 overflow-hidden sm:flex-row">
                <button class="stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white" data-tab="stock-in-form">Stock In</button>
                <button class="stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50" data-tab="stock-in-history">Movement History</button>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl flex-none bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>
                <div class="grow">
                    <p class="text-sm text-gray-500 text-end">Stock In Today</p>
                    <p class="text-3xl font-bold text-gray-900 text-end">{{ $todayCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 flex-none rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                    </svg>
                </div>
                <div class="grow">
                    <p class="text-sm text-gray-500 text-end">Total Items</p>
                    <p class="text-3xl font-bold text-gray-900 text-end">{{ $items->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock In Form Tab --}}
    <div id="tab-stock-in-form">
        {{-- Search --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input id="stockInSearch" type="text" placeholder="Search items to stock in..." oninput="filterStockInItems()" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
        </div>

        {{-- Items Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Current Stock</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $item)
                    <tr class="stockin-item-row hover:bg-gray-50 transition-colors" data-name="{{ strtolower($item->name) }}">
                        <td class="px-6 py-4">
                            @if($item->image_path)
                                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75z"/></svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $item->name }}</p>
                            <span class="inline-flex items-center mt-0.5 px-1.5 py-0.5 rounded text-xs font-mono font-semibold bg-gray-100 text-gray-700 border border-gray-200">{{ $item->item_code }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->category }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="font-bold text-gray-900">{{ number_format($item->stock) }}</span>
                            <span class="text-gray-400">{{ $item->unit }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openStockInModal({{ json_encode(['id' => $item->Product_Id, 'name' => $item->name, 'code' => $item->item_code, 'stock' => $item->stock, 'unit' => $item->unit, 'image' => $item->image_path ? asset('storage/'.$item->image_path) : null]) }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Stock In
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    {{-- END tab-stock-in-form --}}

    {{-- Movement History Tab --}}
    <div id="tab-stock-in-history" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input id="stockInHistorySearch" type="text" placeholder="Search history..." oninput="filterStockInHistory()" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference No.</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Change</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Performed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $txn)
                    <tr class="stockin-history-row hover:bg-gray-50 transition-colors" data-name="{{ strtolower($txn->inventoryItem->name ?? '') }}">
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                                {{ $txn->created_at->format('m/d/Y') }} <span class="text-gray-400">{{ $txn->created_at->format('h:i A') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-mono bg-gray-50 border border-gray-200">{{ $txn->reference_number }}</span>
                        </td>
                        <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">{{ $txn->inventoryItem->name ?? 'N/A' }}</p>
                        @if($txn->inventoryItem)
                            <span class="inline-flex items-center mt-0.5 px-1.5 py-0.5 rounded text-xs font-mono font-semibold bg-gray-100 text-gray-700 border border-gray-200">{{ $txn->inventoryItem->item_code }}</span>
                        @endif
                    </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <span class="text-gray-500">{{ number_format($txn->previous_stock) }}</span>
                                <svg class="w-3 h-3 inline mx-1 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                <span class="font-bold text-green-600">{{ number_format($txn->new_stock) }}</span>
                                <span class="text-xs text-green-500 ml-1">(+{{ number_format($txn->quantity) }})</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $txn->supplier->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $txn->notes ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $txn->creator->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">No stock in transactions yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

{{-- Stock In Modal --}}
@include('pages.Modals.stockInModal')

@endsection

@push('scripts')
@vite('resources/js/pages/stock-in.js')
@endpush
