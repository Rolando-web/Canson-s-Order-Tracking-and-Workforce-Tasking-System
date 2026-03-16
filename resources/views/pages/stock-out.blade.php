@extends('partials.app', ['title' => 'Stock Out - Canson', 'activePage' => 'stock-out'])

@push('styles')
    @vite('resources/css/pages/inventory.css')
@endpush

@section('content')
<div class="inventory-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Stock Out History</h2>
            <p class="text-gray-500 mt-1">Track all stock out movements and transactions</p>
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
                <div class="w-10 h-10 flex-none rounded-xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <div class="grow">
                    <p class="text-sm text-gray-500 text-end">Total Batches</p>
                    <p class="text-3xl font-bold text-gray-900 text-end">{{ $batches->count() }}</p>
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
            <p class="text-sm font-semibold text-amber-800">Automatic Stock Deduction</p>
            <p class="text-xs text-amber-600 mt-0.5">Stock is automatically deducted when orders are assigned to employees. All stock out transactions are recorded here for tracking purposes.</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input id="stockOutHistorySearch" type="text" placeholder="Search by reference or reason..." oninput="filterStockOutHistory()" class="w-full sm:w-md pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
        </div>
    </div>

    {{-- History Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference No.</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Qty</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Performed By</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($batches as $batch)
                @php
                    $batchData = [
                        'reference'     => $batch->reference_number,
                        'date'          => $batch->created_at->format('m/d/Y h:i A'),
                        'reason'        => $batch->reason ?? '—',
                        'notes'         => $batch->notes ?? '—',
                        'created_by'    => $batch->creator?->name ?? 'System',
                        'order_number'  => $batch->order_number,
                        'customer_name' => $batch->customer_name,
                        'order_status'  => $batch->order_status,
                        'phases'        => $batch->phases,
                        'items'         => $batch->items->map(fn($i) => [
                            'name'     => $i->product?->name ?? 'N/A',
                            'code'     => $i->product?->item_code ?? '—',
                            'unit'     => $i->product?->unit ?? '',
                            'prev'     => $i->previous_stock,
                            'qty'      => $i->quantity,
                            'new'      => $i->new_stock,
                        ])->values()->toArray(),
                    ];
                @endphp
                <tr class="stockout-history-row hover:bg-gray-50 transition-colors"
                    data-ref="{{ strtolower($batch->reference_number) }}"
                    data-reason="{{ strtolower($batch->reason ?? '') }}">
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
                            <span class="w-5 h-5 rounded-full bg-red-100 text-red-700 text-xs flex items-center justify-center font-bold">{{ $batch->item_count }}</span>
                            item{{ $batch->item_count !== 1 ? 's' : '' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="font-bold text-red-600">-{{ number_format($batch->total_qty) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $batch->reason ?? 'Order Assignment' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $batch->creator?->name ?? 'System' }}</td>
                    <td class="px-6 py-4">
                        <button onclick='openStockOutDetailModal({{ json_encode($batchData) }})'
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            View Details
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">No stock out transactions yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- Stock Out Batch Detail Modal --}}
@include('pages.Modals.stockOutDetailModal')

@endsection

@push('scripts')
@vite('resources/js/pages/stock-out.js')
@endpush
