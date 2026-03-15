@extends('partials.app', ['title' => 'Stock In - Canson', 'activePage' => 'stock-in'])

@push('styles')
    @vite('resources/css/pages/inventory.css')
@endpush

@section('content')
<div class="inventory-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 id="stockin-page-title" class="text-2xl font-bold text-gray-900">Stock In</h2>
            <p id="stockin-page-subtitle" class="text-gray-500 mt-1">Add incoming stock to inventory</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex flex-col rounded-lg border border-gray-300 overflow-hidden sm:flex-row">
                <button class="stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white" data-tab="stock-in-form">Stock In</button>
                <button class="stockin-tab-btn p-2 border-x border-gray-300 text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50" data-tab="stock-in-history">Movement History</button>
                <button class="stockin-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50" data-tab="suppliers">Suppliers</button>
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
        <div class="grid grid-cols-1 xl:grid-cols-5 gap-5 items-start">

            {{-- LEFT: Batch Panel (2 cols) --}}
            <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden xl:sticky xl:top-4">
                {{-- Panel Header --}}
                <div class="bg-gradient-to-r from-emerald-50 to-green-50 border-b border-emerald-100 px-5 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Batch Stock In</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Fill details & add items below</p>
                            </div>
                        </div>
                        <span id="batchCount" class="text-xs font-bold text-emerald-700 bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded-full">0 items</span>
                    </div>
                </div>

                {{-- Panel Body --}}
                <div class="p-5 space-y-4">
                    {{-- Supplier / Date --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Supplier <span class="text-red-500">*</span></label>
                            <select id="batchSupplier" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->Supplier_Id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date Received <span class="text-red-500">*</span></label>
                            <input type="date" id="batchDate" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Notes / Remarks</label>
                        <textarea id="batchNotes" rows="2" placeholder="e.g. Weekly supplier delivery, batch #123..."
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-dashed border-gray-200"></div>

                    {{-- Batch Items List --}}
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">Items to Stock In</span>
                            <button onclick="clearBatch()" class="text-xs text-red-400 hover:text-red-600 font-medium transition-colors">Clear All</button>
                        </div>
                        <div id="batchItemsList" class="space-y-2 max-h-[320px] overflow-y-auto pr-1">
                            <div id="batchEmptyState" class="text-center py-8 border-2 border-dashed border-gray-200 rounded-xl">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                <p class="text-sm text-gray-400">No items added yet</p>
                                <p class="text-xs text-gray-300 mt-1">Click <strong class="text-gray-400">+ Add</strong> from the product list</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Footer --}}
                <div class="border-t border-gray-100 bg-gray-50/50 px-5 py-4">
                    <button onclick="submitBatchStockIn()" id="batchSubmitBtn"
                        class="w-full px-5 py-3 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 active:bg-emerald-800 transition-all shadow-sm hover:shadow flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="batchSubmitText">Submit Stock In</span>
                    </button>
                </div>
            </div>

            {{-- RIGHT: Product Search & List (3 cols) --}}
            <div class="xl:col-span-3 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                {{-- Product Panel Header --}}
                <div class="bg-gray-50 border-b border-gray-200 px-5 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gray-200 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Product List</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $items->count() }} products available</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Search --}}
                <div class="px-5 py-3 border-b border-gray-100">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        <input id="stockInSearch" type="text" placeholder="Search products..." oninput="filterStockInItems()" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    </div>
                </div>

                {{-- Product Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px]">
                        <thead>
                            <tr class="bg-gray-50/60 border-b border-gray-200">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $item)
                            <tr class="stockin-item-row hover:bg-emerald-50/40 transition-colors" data-id="{{ $item->Product_Id }}" data-name="{{ strtolower($item->name) }}">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($item->image_path)
                                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="w-9 h-9 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                        @else
                                            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4.5 h-4.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75z"/></svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $item->name }}</p>
                                            <span class="text-xs font-mono text-gray-400">{{ $item->item_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600">{{ $item->category }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    <span class="font-bold text-gray-900">{{ number_format($item->stock) }}</span>
                                    <span class="text-gray-400 text-xs ml-0.5">{{ $item->unit }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button id="add-btn-{{ $item->Product_Id }}"
                                        onclick="addToBatch({{ json_encode(['id' => $item->Product_Id, 'name' => $item->name, 'code' => $item->item_code, 'stock' => $item->stock, 'unit' => $item->unit]) }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        Add
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    {{-- END tab-stock-in-form --}}

    {{-- Movement History Tab --}}
    <div id="tab-stock-in-history" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input id="stockInHistorySearch" type="text" placeholder="Search by reference or supplier..." oninput="filterStockInHistory()" class="w-full sm:w-md pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference No.</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Qty</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Performed By</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($batches as $batch)
                    @php
                        $batchData = [
                            'reference'  => $batch->reference_number,
                            'date'       => $batch->created_at->format('m/d/Y h:i A'),
                            'supplier'   => $batch->supplier?->name ?? '—',
                            'notes'      => $batch->notes ?? '—',
                            'created_by' => $batch->creator?->name ?? 'System',
                            'items'      => $batch->items->map(fn($i) => [
                                'name'      => $i->product?->name ?? 'N/A',
                                'code'      => $i->product?->item_code ?? '—',
                                'unit'      => $i->product?->unit ?? '',
                                'prev'      => $i->previous_stock,
                                'qty'       => $i->quantity,
                                'new'       => $i->new_stock,
                                'unit_cost' => $i->unit_cost ?? 0,
                            ])->values()->toArray(),
                        ];
                    @endphp
                    <tr class="stockin-history-row hover:bg-gray-50 transition-colors"
                        data-ref="{{ strtolower($batch->reference_number) }}"
                        data-supplier="{{ strtolower($batch->supplier?->name ?? '') }}">
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div>
                                <p class="font-medium text-gray-800">{{ $batch->created_at->format('m/d/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $batch->created_at->format('h:i A') }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-mono bg-gray-50 border border-gray-200 text-gray-700">{{ $batch->reference_number }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 text-sm font-semibold text-gray-800">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-xs flex items-center justify-center font-bold">{{ $batch->item_count }}</span>
                                item{{ $batch->item_count !== 1 ? 's' : '' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="font-bold text-green-600">+{{ number_format($batch->total_qty) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $batch->supplier?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $batch->creator?->name ?? 'System' }}</td>
                        <td class="px-6 py-4">
                            <button onclick='openStockInDetailModal({{ json_encode($batchData) }})'
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                View Details
                            </button>
                        </td>
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

    {{-- Suppliers Tab --}}
    <div id="tab-suppliers" class="hidden">
      <div class="mb-6 flex justify-between items-center p-4 bg-white rounded-xl border border-gray-200">
         {{-- Supplier Type Toggle --}}
         <div class="flex bg-gray-100 p-1 rounded-lg">
            <button id="activeSupplierBtn" onclick="toggleSupplierView('active')" class="px-4 py-2 text-sm font-medium rounded-md transition-colors bg-emerald-600 text-white">
                Active Suppliers
            </button>
            <button id="archivedSupplierBtn" onclick="toggleSupplierView('archived')" class="px-4 py-2 text-sm font-medium rounded-md transition-colors text-gray-600 hover:text-gray-900">
                Archived Suppliers
            </button>
         </div>

         <button id="addSupplierBtn" onclick="openAddSupplierModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add Supplier
        </button>
      </div>

        {{-- Search Bar --}}
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input id="supplierSearch" type="text" placeholder="Search suppliers..." oninput="filterSuppliers()" class="w-full sm:w-md pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Active Suppliers Table --}}
        <div id="activeSupplierTable" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Address</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suppliers as $supplier)
                    <tr class="supplier-row active-supplier-row hover:bg-gray-50 transition-colors" data-name="{{ strtolower($supplier->name) }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-bold text-emerald-600">{{ strtoupper(substr($supplier->name, 0, 1)) }}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">{{ $supplier->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $supplier->address }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $supplier->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $supplier->phone }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <button onclick="openEditSupplierModal({{ json_encode(['id' => $supplier->Supplier_Id, 'name' => $supplier->name, 'address' => $supplier->address, 'email' => $supplier->email, 'phone' => $supplier->phone]) }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    Edit
                                </button>
                                <button onclick="deleteSupplier({{ $supplier->Supplier_Id }}, '{{ addslashes($supplier->name) }}')"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    Remove
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">No active suppliers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- Archived Suppliers Table --}}
        <div id="archivedSupplierTable" class="bg-white rounded-xl border border-gray-200 overflow-hidden hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Address</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($archivedSuppliers as $supplier)
                    <tr class="supplier-row archived-supplier-row bg-gray-50" data-name="{{ strtolower($supplier->name) }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-bold text-gray-500">{{ strtoupper(substr($supplier->name, 0, 1)) }}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-600">{{ $supplier->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $supplier->address }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $supplier->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $supplier->phone }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                </svg>
                                Archived
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">No archived suppliers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

{{-- Stock In Batch Detail Modal --}}
@include('pages.Modals.stockInDetailModal')

{{-- Add/Edit Supplier Modal --}}
@include('pages.Modals.supplierModal')

@endsection

@push('scripts')
@vite('resources/js/pages/stock-in.js')
@endpush
