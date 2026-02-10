@extends('partials.app', ['title' => 'Orders - Canson', 'activePage' => 'orders'])

@push('styles')
    @vite('resources/css/pages/orders.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/orders.js')
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
<div class="orders-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Orders</h2>
            <p class="text-gray-500 mt-1">Manage and track customer orders</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- View Toggle --}}
            <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                <button class="px-3 py-2 bg-gray-100 text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                </button>
                <button class="px-3 py-2 bg-white text-gray-400 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                </button>
            </div>
            <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Order
            </button>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
        <div class="flex items-center gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" placeholder="Search orders, customers..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                <select class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option>All Status</option>
                    <option>Pending</option>
                    <option>In-Progress</option>
                    <option>Completed</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned To</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php
                    $orders = [
                        ['id' => 'ORD-001', 'customer' => 'St. Mary School', 'items' => '500 Data Filer Boxes', 'status' => 'Completed', 'assigned' => 'Juan Dela Cruz', 'initial' => 'J', 'priority' => 'Normal', 'priorityColor' => 'bg-emerald-50 text-emerald-600 border-emerald-200'],
                        ['id' => 'ORD-002', 'customer' => 'City High', 'items' => '200 Whiteboards', 'status' => 'In-Progress', 'assigned' => 'Maria Santos', 'initial' => 'M', 'priority' => 'High', 'priorityColor' => 'bg-red-50 text-red-600 border-red-200'],
                        ['id' => 'ORD-003', 'customer' => 'Office Depot', 'items' => '1000 Storage Boxes', 'status' => 'Pending', 'assigned' => null, 'initial' => '', 'priority' => 'Normal', 'priorityColor' => 'bg-emerald-50 text-emerald-600 border-emerald-200'],
                        ['id' => 'ORD-004', 'customer' => 'Learning Tree', 'items' => '50 Whiteboards', 'status' => 'Pending', 'assigned' => null, 'initial' => '', 'priority' => 'Urgent', 'priorityColor' => 'bg-red-50 text-red-600 border-red-200'],
                        ['id' => 'ORD-005', 'customer' => 'Gov. Office', 'items' => '300 Filer Boxes', 'status' => 'In-Progress', 'assigned' => 'Ana Lim', 'initial' => 'A', 'priority' => 'High', 'priorityColor' => 'bg-red-50 text-red-600 border-red-200'],
                    ];
                @endphp
                @foreach($orders as $order)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $order['id'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $order['customer'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $order['items'] }}</td>
                    <td class="px-6 py-4">
                        @php
                            $statusColor = match($order['status']) {
                                'Completed' => 'bg-green-50 text-green-600 border-green-200',
                                'In-Progress' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                'Pending' => 'bg-gray-50 text-gray-500 border-gray-200',
                                default => 'bg-gray-50 text-gray-500 border-gray-200',
                            };
                        @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColor }}">{{ $order['status'] }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($order['assigned'])
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">{{ $order['initial'] }}</div>
                            {{ $order['assigned'] }}
                        </div>
                        @else
                        <span class="text-gray-400 italic">Unassigned</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border {{ $order['priorityColor'] }}">{{ $order['priority'] }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <button class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 3.75H5.25"/></svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
