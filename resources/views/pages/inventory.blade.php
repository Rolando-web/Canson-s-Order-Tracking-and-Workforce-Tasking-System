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
            <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
        </div>
    </div>
@endsection

@section('content')
<div class="inventory-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Inventory Management</h2>
            <p class="text-gray-500 mt-1">Track stock levels across all items</p>
        </div>
    </div>

    <div>
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
</div>

@endsection
