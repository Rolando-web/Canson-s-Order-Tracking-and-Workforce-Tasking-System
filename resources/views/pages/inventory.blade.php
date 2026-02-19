@extends('partials.app', ['title' => 'Inventory - Canson', 'activePage' => 'inventory'])

@push('styles')
    @vite('resources/css/pages/inventory.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/inventory.js')
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
<div class="inventory-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Inventory Management</h2>
            <p class="text-gray-500 mt-1">Track stock levels and movement history</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openAddProductModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add Product
            </button>
            <div class="flex flex-col rounded-lg border border-gray-300 overflow-hidden sm:flex-row">
                <button class="inventory-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 bg-emerald-600 text-white" data-tab="current-stock">Current Stock</button>
                <button class="inventory-tab-btn p-2 text-sm font-medium md:px-5 md:py-2.5 md:text-sm bg-white text-gray-600 hover:bg-gray-50" data-tab="movement-history">Movement History</button>
            </div>
        </div>
    </div>

    {{-- Current Stock Tab --}}
    <div id="tab-current-stock">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border-2 border-emerald-500 p-5">
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
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input id="inventorySearch" type="text" placeholder="Search inventory..." oninput="filterInventory()" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>
                <select id="inventoryCategoryFilter" onchange="filterInventory()" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    <option value="Finished Goods">Finished Goods</option>
                    <option value="Raw Materials">Raw Materials</option>
                    <option value="Packaging Materials">Packaging Materials</option>
                </select>
                <select id="inventoryStockFilter" onchange="filterInventory()" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">All Stock Levels</option>
                    <option value="high">High Stock (&gt;100)</option>
                    <option value="normal">Normal (21–100)</option>
                    <option value="low">Low Stock (≤20)</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
        </div>

        {{-- Inventory Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Level</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $item)
                    @php
                        $stockLevel = $item->stock === 0 ? 'out' : ($item->stock <= 20 ? 'low' : ($item->stock <= 100 ? 'normal' : 'high'));
                    @endphp
                    <tr class="inventory-row hover:bg-gray-50 transition-colors" data-category="{{ $item->category }}" data-stock-level="{{ $stockLevel }}" data-name="{{ strtolower($item->name) }}">
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
                            <div class="flex items-center gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $item->item_id }}</p>
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
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">₱—</td>
                        <td class="px-6 py-4 text-sm"><span class="font-bold text-gray-900">{{ number_format($item->stock) }}</span> <span class="text-gray-400">{{ $item->unit }}</span></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-200">{{ $item->status }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <button
                                    onclick="openStockInModal({{ json_encode(['name' => $item->name, 'code' => $item->item_id, 'stock' => $item->stock, 'unit' => $item->unit, 'image' => $item->image_path ? asset('storage/'.$item->image_path) : null]) }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Stock In
                                </button>
                                <button
                                    onclick="openStockOutModal({{ json_encode(['name' => $item->name, 'code' => $item->item_id, 'stock' => $item->stock, 'unit' => $item->unit, 'image' => $item->image_path ? asset('storage/'.$item->image_path) : null]) }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Stock Out
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Movement History Tab --}}
    <div id="tab-movement-history" class="hidden">
        {{-- Search --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" placeholder="Search history..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
        </div>

        {{-- History Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference No.</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Change</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Note / Reason</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Performed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                                2/9/2026 <span class="text-gray-400">10:21 PM</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-mono bg-gray-50 border border-gray-200">REF-2026-001</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">Corrugated Board Sheets</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-200">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                STOCK IN
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <span class="text-gray-500">1,000</span>
                                <svg class="w-3 h-3 inline mx-1 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                <span class="font-bold text-green-600">1,500</span>
                                <span class="text-xs text-gray-400 ml-1">(+500)</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">Weekly supplier delivery</td>
                        <td class="px-6 py-4 text-sm text-gray-600">System Admin</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                                2/8/2026 <span class="text-gray-400">10:21 PM</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-mono bg-gray-50 border border-gray-200">ORD-8821</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">Data Filer Box (Blue)</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-200">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                STOCK OUT
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <span class="text-gray-500">150</span>
                                <svg class="w-3 h-3 inline mx-1 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                <span class="font-bold text-red-600">100</span>
                                <span class="text-xs text-gray-400 ml-1">(-50)</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">Order fulfillment</td>
                        <td class="px-6 py-4 text-sm text-gray-600">System Admin</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modals --}}
@include('pages.Modals.stockInModal')
@include('pages.Modals.stockOutModal')
@include('pages.Modals.addProductModal')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.inventory-tab-btn');
    const currentStock = document.getElementById('tab-current-stock');
    const movementHistory = document.getElementById('tab-movement-history');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => { t.className = 'inventory-tab-btn px-5 py-2.5 text-sm font-medium bg-white text-gray-600 hover:bg-gray-50'; });
            tab.className = 'inventory-tab-btn px-5 py-2.5 text-sm font-medium bg-emerald-600 text-white';

            if (tab.dataset.tab === 'current-stock') {
                currentStock.classList.remove('hidden');
                movementHistory.classList.add('hidden');
            } else {
                currentStock.classList.add('hidden');
                movementHistory.classList.remove('hidden');
            }
        });
    });
});
</script>
@endpush
