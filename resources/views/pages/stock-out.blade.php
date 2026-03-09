@extends('partials.app', ['title' => 'Stock Out - Canson', 'activePage' => 'stock-out'])

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
            <h2 class="text-2xl font-bold text-gray-900">Stock Out</h2>
            <p class="text-gray-500 mt-1">Automatic stock out when orders are assigned to employees</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl flex-none bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="grow">
                    <p class="text-sm text-gray-500 text-end">Stock Out Today</p>
                    <p class="text-3xl font-bold text-gray-900 text-end">{{ $todayCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 flex-none rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                    </svg>
                </div>
                <div class="grow">
                    <p class="text-sm text-gray-500 text-end">Total Stock Outs</p>
                    <p class="text-3xl font-bold text-gray-900 text-end">{{ $transactions->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-800">Automatic Stock Adjustment</p>
            <p class="text-xs text-amber-600 mt-0.5">Stock is automatically deducted when a manager assigns an order to an employee. Each order assignment triggers stock out for the items in that order.</p>
        </div>
    </div>

    {{-- Movement History --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input id="stockOutHistorySearch" type="text" placeholder="Search stock out history..." oninput="filterStockOutHistory()" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[950px]">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference No.</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item ID</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Change</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Performed By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $txn)
                <tr class="stockout-history-row hover:bg-gray-50 transition-colors" data-name="{{ strtolower($txn->inventoryItem->name ?? '') }}">
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
                        <span class="inline-flex px-2 py-1 rounded text-xs font-mono font-semibold bg-gray-100 text-gray-700 border border-gray-200">{{ $txn->inventoryItem->item_code ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $txn->inventoryItem->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 text-sm font-bold text-red-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                            -{{ number_format($txn->quantity) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $txn->reason ?? 'Order Assignment' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $txn->notes ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $txn->creator->name ?? 'System' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-400">No stock out transactions yet. Stock is automatically deducted when orders are assigned.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/stock-out.js')
@endpush
