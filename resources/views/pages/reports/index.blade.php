@extends('partials.app', ['title' => 'Reports - Canson', 'activePage' => 'reports'])

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
<div class="reports-page">

    {{-- ── Page Header ── --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Reports</h2>
            <p class="text-gray-500 mt-1">Overview of sales, orders, and business performance.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sales.print') }}" target="_blank"
               class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/>
                </svg>
                Print Report
            </a>
            <a href="{{ route('sales.exportCsv') }}"
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm shadow-emerald-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- ── Quick Links ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('sales') }}"
           class="bg-white border border-gray-200 rounded-xl p-5 hover:border-emerald-300 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors">Sales Report</h3>
                    <p class="text-xs text-gray-400 mt-0.5">All transactions & revenue</p>
                </div>
                <svg class="w-4 h-4 text-gray-400 ml-auto group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('analytics') }}"
           class="bg-white border border-gray-200 rounded-xl p-5 hover:border-blue-300 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">Analytics Report</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Charts, KPIs & insights</p>
                </div>
                <svg class="w-4 h-4 text-gray-400 ml-auto group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('sales.print') }}" target="_blank"
           class="bg-white border border-gray-200 rounded-xl p-5 hover:border-purple-300 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">Print Report</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Printable sales summary</p>
                </div>
                <svg class="w-4 h-4 text-gray-400 ml-auto group-hover:text-purple-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </div>
        </a>
    </div>

    {{-- ── Summary KPIs ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <p class="text-xs text-gray-500 mb-1">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalRevenue ?? 0) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">All-time</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <p class="text-xs text-gray-500 mb-1">Total Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalOrders ?? 0) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">All-time</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <p class="text-xs text-gray-500 mb-1">This Month Revenue</p>
            <p class="text-2xl font-bold text-gray-900">₱{{ number_format($thisMonthRevenue ?? 0) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">{{ now()->format('F Y') }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <p class="text-xs text-gray-500 mb-1">Today's Sales</p>
            <p class="text-2xl font-bold text-gray-900">₱{{ number_format($todaySales ?? 0) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">{{ now()->format('M d, Y') }}</p>
        </div>
    </div>

    {{-- ── Monthly Revenue Bar Chart ── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Monthly Revenue (Last 12 Months)</h3>
        @php
            $chartVals = array_values($monthlyRevenue ?? []);
            $chartLabels = array_keys($monthlyRevenue ?? []);
            $chartMax = max($chartVals ?: [1]);
        @endphp
        <div class="flex items-end justify-between gap-2 h-48 px-2">
            @foreach($chartVals as $i => $val)
            <div class="flex flex-col items-center gap-2 flex-1">
                <div class="w-full flex items-end justify-center h-40">
                    <div class="w-full rounded-t-md bg-emerald-500 hover:bg-emerald-600 transition-colors cursor-default"
                         style="height: {{ $chartMax > 0 ? ($val/$chartMax)*100 : 0 }}%"
                         title="{{ $chartLabels[$i] }}: ₱{{ number_format($val) }}"></div>
                </div>
                <span class="text-[9px] text-gray-500">{{ $chartLabels[$i] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- ── Order Status Distribution ── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Order Status Distribution</h3>
            <div class="space-y-3">
                @php
                    $statusColors = ['Pending' => 'bg-gray-400', 'In-Progress' => 'bg-amber-400', 'Ready for Delivery' => 'bg-blue-500', 'Delivered' => 'bg-teal-500', 'Completed' => 'bg-emerald-500'];
                    $statusTotal  = array_sum($statusCounts ?? []) ?: 1;
                @endphp
                @foreach($statusCounts ?? [] as $status => $count)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-700">{{ $status }}</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="{{ $statusColors[$status] ?? 'bg-gray-400' }} h-2 rounded-full"
                             style="width: {{ round(($count / $statusTotal) * 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── Top Products ── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top Products by Revenue</h3>
            <div class="space-y-3">
                @forelse($topProducts ?? [] as $i => $product)
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-400 w-4">{{ $i + 1 }}</span>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-sm font-medium text-gray-800">{{ $product->name }}</span>
                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($product->total_revenue) }}</span>
                        </div>
                        <p class="text-xs text-gray-400">{{ number_format($product->total_sold) }} units sold</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">No product data available</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Recent Orders ── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900">Recent Orders</h3>
            <a href="{{ route('sales') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium flex items-center gap-1">
                View all
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Transaction</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentOrders ?? [] as $order)
                @php
                    $badgeClass = match($order['status']) {
                        'Completed'         => 'bg-green-50 text-green-700',
                        'In-Progress'       => 'bg-emerald-50 text-emerald-700',
                        'Ready for Delivery'=> 'bg-blue-50 text-blue-700',
                        'Delivered'         => 'bg-teal-50 text-teal-700',
                        default             => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">{{ $order['id'] }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-700">{{ $order['customer'] }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ Str::limit($order['items'], 30) }}</td>
                    <td class="px-5 py-3.5 text-sm font-bold text-gray-900 text-right">₱{{ number_format($order['amount']) }}</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">{{ $order['status'] }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $order['date'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-400">No recent orders</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
