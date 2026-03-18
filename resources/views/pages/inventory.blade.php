@extends('partials.app', ['title' => 'Inventory - Canson', 'activePage' => 'inventory'])

@push('styles')
    @vite('resources/css/pages/inventory.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/inventory.js')
@endpush

@section('content')
<div class="inventory-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 id="inv-page-title" class="text-2xl font-bold text-gray-900">Inventory Management</h2>
            <p id="inv-page-subtitle" class="text-gray-500 mt-1">Track stock levels across all items</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex flex-col rounded-lg border border-gray-300 overflow-hidden sm:flex-row">
                <button class="inv-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white" data-tab="inv-stock">Inventory</button>
                <button class="inv-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-white text-gray-600 hover:bg-gray-50" data-tab="inv-reports">Reports</button>
            </div>
        </div>
    </div>

    {{-- ===== INVENTORY TAB ===== --}}
    <div id="tab-inv-stock">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl flex-none bg-emerald-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                        </svg>
                    </div>
                    <div class="grow">
                        <p class="text-sm text-gray-500 text-end">Total Items</p>
                        <p class="text-3xl font-bold text-gray-900 text-end">{{ $totalItems }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center">
                    <div class="w-10 h-10 flex-none rounded-xl bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                        </svg>
                    </div>
                    <div class="grow">
                        <p class="text-sm text-gray-500 text-end">Low Stock Alert</p>
                        <p class="text-3xl font-bold text-gray-900 text-end">{{ $lowStockAlert }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center">
                    <div class="w-10 h-10 flex-none rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
                        </svg>
                    </div>
                    <div class="grow">
                        <p class="text-sm text-gray-500 text-end">Transactions Today</p>
                        <p class="text-3xl font-bold text-gray-900 text-end">0</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search & Filters --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
            <div class="flex flex-col md:flex-row items-stretch gap-3">
                <div class="relative flex-1 xl:flex-none">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input id="inventorySearch" type="text" placeholder="Search inventory..." oninput="filterInventory()" class="w-full xl:w-sm pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-transparent">
                </div>
                <select id="inventoryCategoryFilter" onchange="filterInventory()" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    <option value="Finished Goods">Finished Goods</option>
                    <option value="Raw Materials">Raw Materials</option>
                    <option value="Packaging Materials">Packaging Materials</option>
                </select>
                <select id="inventoryStockFilter" onchange="filterInventory()" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">All Stock Levels</option>
                    <option value="In Stock">In Stock</option>
                    <option value="Low Stock">Low Stock</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>
        </div>

        {{-- Inventory Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Level</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $item)
                    <tr class="inventory-row hover:bg-gray-50 transition-colors" data-category="{{ $item->category }}" data-stock-level="{{ $item->status }}" data-name="{{ strtolower($item->name) }}">
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
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-mono font-semibold bg-gray-100 text-gray-700 border border-gray-200">INV-{{ str_pad($item->Product_Id, 4, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $item->name }}</p>
                                </div>
                                @if($item->is_best_seller)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-300">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Most Sold
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->category }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">₱{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-6 py-4 text-sm"><span class="font-bold text-gray-900">{{ number_format($item->stock) }}</span> <span class="text-gray-400">{{ $item->unit }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    {{-- END INVENTORY TAB --}}

    {{-- ===== REPORTS TAB ===== --}}
    <div id="tab-inv-reports" class="hidden">

        {{-- Stock Valuation by Category --}}
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-3">Stock Valuation by Category</h3>
            <div class="grid grid-cols-1 sm:grid-cols-{{ $valuationByCategory->count() }} gap-4 mb-4">
                @foreach($valuationByCategory as $cat)
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ $cat->category }}</p>
                    <p class="text-2xl font-bold text-gray-900">₱{{ number_format($cat->total_value, 2) }}</p>
                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                        <span>{{ $cat->item_count }} items</span>
                        <span class="text-gray-300">|</span>
                        <span>{{ number_format($cat->total_stock) }} units</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Low Stock Items --}}
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-3">Low Stock & Out of Stock Items</h3>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-red-50/50">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Reorder Point</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($lowStockItems as $item)
                        <tr class="hover:bg-red-50/30 transition-colors">
                            <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 py-3 text-xs font-mono text-gray-500">{{ $item->item_code }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $item->category }}</td>
                            <td class="px-6 py-3 text-sm font-bold text-right {{ $item->stock <= 0 ? 'text-red-600' : 'text-amber-600' }}">{{ number_format($item->stock) }} {{ $item->unit }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500 text-right">{{ $item->reorder_point ?? 50 }}</td>
                            <td class="px-6 py-3">
                                @if($item->status === 'Out of Stock')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">Out of Stock</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">Low Stock</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">All items are well stocked!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- Recent Stock Movements --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Stock In --}}
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-3">Recent Stock In</h3>
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-emerald-50/50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentStockIn as $si)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $si->created_at->format('m/d/Y') }}</td>
                                <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $si->product?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2.5 text-sm font-bold text-green-600 text-right">+{{ number_format($si->quantity) }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-500 text-right">{{ $si->unit_cost > 0 ? '₱' . number_format($si->unit_cost, 2) : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">No stock in records yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            {{-- Recent Stock Out --}}
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-3">Recent Stock Out</h3>
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-red-50/50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentStockOut as $so)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $so->created_at->format('m/d/Y') }}</td>
                                <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $so->product?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2.5 text-sm font-bold text-red-600 text-right">-{{ number_format($so->quantity) }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $so->reason ?? 'Order Assignment' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">No stock out records yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- END REPORTS TAB --}}
</div>

@endsection
