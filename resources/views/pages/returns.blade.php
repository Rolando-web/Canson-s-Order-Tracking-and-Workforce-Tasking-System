@extends('partials.app', ['title' => 'Cover Items - Canson', 'activePage' => 'returns'])

@push('styles')
    @vite('resources/css/pages/inventory.css')
@endpush

@section('content')
<div class="inventory-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Cover Items</h2>
            <p class="text-gray-500 mt-1">Damaged items from delivery — replacements are automatically included in the customer's next order</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl flex-none bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <div class="grow">
                    <p class="text-sm text-gray-500 text-end">Pending</p>
                    <p class="text-3xl font-bold text-gray-900 text-end">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl flex-none bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="grow">
                    <p class="text-sm text-gray-500 text-end">Covered</p>
                    <p class="text-3xl font-bold text-gray-900 text-end">{{ $stats['covered'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl flex-none bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5"/></svg>
                </div>
                <div class="grow">
                    <p class="text-sm text-gray-500 text-end">Total</p>
                    <p class="text-3xl font-bold text-gray-900 text-end">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
        <div class="flex sm:flex-row flex-col gap-3">
            <div class="relative max-w-md flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input id="claimSearch" type="text" placeholder="Search by customer, item, or claim ID..." oninput="filterClaims()" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="flex-1">
                <select id="statusFilter" onchange="filterClaims()" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="all">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Covered">Covered</option>
            </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[800px]">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Claim ID</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Damaged Item</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Damage Reason</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Original Order</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Covered By</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($claims as $claim)
                <tr class="claim-row hover:bg-gray-50 transition-colors"
                    data-search="{{ strtolower($claim->return_id . ' ' . $claim->customer_name . ' ' . ($claim->product->name ?? '')) }}"
                    data-status="{{ $claim->status }}">
                    <td class="px-5 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-600 border border-amber-200">{{ $claim->return_id }}</span>
                    </td>
                    <td class="px-5 py-3 text-sm font-semibold text-gray-900">{{ $claim->customer_name }}</td>
                    <td class="px-5 py-3 text-sm text-gray-700">{{ $claim->product->name ?? 'N/A' }}</td>
                    <td class="px-5 py-3 text-sm font-semibold text-gray-900">{{ $claim->quantity }}</td>
                    <td class="px-5 py-3 text-sm text-gray-600 max-w-[180px] truncate" title="{{ $claim->reason }}">{{ Str::limit($claim->reason, 30) }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $claim->order_reference ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @php
                            $statusColor = match($claim->status) {
                                'Pending'  => 'bg-amber-50 text-amber-600 border-amber-200',
                                'Covered'  => 'bg-green-50 text-green-600 border-green-200',
                                default    => 'bg-gray-50 text-gray-500 border-gray-200',
                            };
                        @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColor }}">{{ $claim->status }}</span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-500">
                        @if($claim->covered_by_order)
                            <span class="px-2 py-0.5 rounded bg-green-50 text-green-700 text-xs font-medium">{{ $claim->covered_by_order }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $claim->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-sm text-gray-400">No damage claims yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/pages/returns.js')
@endpush
