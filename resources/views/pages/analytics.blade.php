@extends('partials.app', ['title' => 'Analytics - Canson', 'activePage' => 'analytics'])

@push('styles')
    @vite('resources/css/pages/analytics.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/analytics.js')
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
<div class="analytics-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Analytics</h2>
            <p class="text-gray-500 mt-1">Sales performance, production metrics & insights.</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Period Filter --}}
            <form method="GET" action="{{ route('analytics') }}" class="flex items-center gap-2">
                <select name="period" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer">
                    <option value="last_7"     {{ ($period ?? 'this_month') === 'last_7'     ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="last_30"    {{ ($period ?? 'this_month') === 'last_30'    ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="this_month" {{ ($period ?? 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter"    {{ ($period ?? 'this_month') === 'quarter'    ? 'selected' : '' }}>This Quarter</option>
                    <option value="this_year"  {{ ($period ?? 'this_month') === 'this_year'  ? 'selected' : '' }}>This Year</option>
                </select>
            </form>
            {{-- Export CSV --}}
            <a href="{{ route('analytics.exportCsv') }}?period={{ $period ?? 'this_month' }}"
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Sales KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 text-end">Monthly Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 text-end">₱{{ number_format($totalRevenue ?? 0) }}</p>
                    <p class="text-[0.6rem] xl:text-xs text-green-500 mt-1 text-end">{{ $completedThisMonth ?? 0 }} completed this month</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 text-end">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 text-end">{{ $totalOrders ?? 0 }}</p>
                    <p class="text-[0.6rem] xl:text-xs text-green-500 mt-1 text-end">all time completed</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 text-end">Avg. Order Value</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 text-end">₱{{ number_format($avgOrderValue ?? 0) }}</p>
                    <p class="text-[0.6rem] xl:text-xs text-green-500 mt-1 text-end">per completed order</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 text-end">Active Customers</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 text-end">{{ $activeCustomers ?? 0 }}</p>
                    <p class="text-[0.6rem] xl:text-xs text-green-500 mt-1 text-end">this month</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Trend Chart --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">Revenue Trend</h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-xs text-gray-500">This Month</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                    <span class="text-xs text-gray-500">Last Month</span>
                </div>
            </div>
        </div>
        <div class="relative h-64">
            {{-- Grid lines --}}
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                @for($i = 0; $i < 5; $i++)
                <div class="border-t border-gray-100 w-full"></div>
                @endfor
            </div>
            {{-- Y-axis labels --}}
            @php
                $maxRevenue = max(array_values($revenueTrend ?? [0])) ?: 1;
                $ySteps = 4;
            @endphp
            <div class="absolute left-0 top-0 h-full flex flex-col justify-between text-xs text-gray-400 -ml-1">
                @for($i = $ySteps; $i >= 0; $i--)
                    @php $val = round(($maxRevenue / $ySteps) * $i); @endphp
                    <span>₱{{ $val >= 1000 ? number_format($val / 1000) . 'K' : $val }}</span>
                @endfor
            </div>
            {{-- Bar chart --}}
            <div class="ml-10 h-full flex items-end justify-between gap-2">
                @foreach($revenueTrend ?? [] as $month => $amount)
                <div class="flex flex-col items-center gap-2 flex-1">
                    <div class="w-full flex items-end justify-center gap-1 h-52">
                        <div class="w-6 bg-emerald-500 rounded-t-md transition-all duration-500" style="height: {{ $maxRevenue > 0 ? ($amount/$maxRevenue)*100 : 0 }}%"></div>
                    </div>
                    <span class="text-[9px] text-gray-500">{{ $month }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Sales by Category --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Sales by Category</h3>
            <div class="flex items-center justify-center gap-8">
                @php
                    $catTotal = array_sum(array_column($salesByCategory ?? [], 'total')) ?: 1;
                    $catColors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899'];
                    $circumference = 2 * 3.14159 * 14;
                    $catOffset = 0;
                @endphp
                <div class="relative w-44 h-44">
                    <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                        @foreach(($salesByCategory ?? []) as $ci => $cat)
                            @php
                                $pct = $cat['total'] / $catTotal;
                                $dashLen = $pct * $circumference;
                                $gapLen = $circumference - $dashLen;
                                $color = $catColors[$ci % count($catColors)];
                            @endphp
                            <circle cx="18" cy="18" r="14" fill="none" stroke="{{ $color }}" stroke-width="4"
                                    stroke-dasharray="{{ round($dashLen, 1) }} {{ round($gapLen, 1) }}" stroke-dashoffset="{{ round(-$catOffset, 1) }}" stroke-linecap="round"/>
                            @php $catOffset += $dashLen; @endphp
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <p class="text-xs text-gray-400">Total</p>
                        <p class="text-lg font-bold text-gray-900">₱{{ $catTotal >= 1000 ? number_format($catTotal / 1000) . 'K' : number_format($catTotal) }}</p>
                    </div>
                </div>
                <div class="space-y-3">
                    @foreach(($salesByCategory ?? []) as $ci => $cat)
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $catColors[$ci % count($catColors)] }}"></span>
                        <div>
                            <p class="text-sm text-gray-500">{{ $cat['category'] }}</p>
                            <p class="font-bold text-gray-900 text-sm">₱{{ number_format($cat['total']) }} <span class="text-xs font-normal text-gray-400">({{ round(($cat['total'] / $catTotal) * 100) }}%)</span></p>
                        </div>
                    </div>
                    @endforeach
                    @if(empty($salesByCategory))
                    <p class="text-sm text-gray-400">No sales data yet</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Order Status Distribution --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Order Status Distribution</h3>
            <div class="flex items-center justify-center gap-8">
                @php
                    $statusTotal = array_sum($orderStatusCounts ?? []) ?: 1;
                    $statusColors = ['Pending' => '#3b82f6', 'In-Progress' => '#f59e0b', 'Completed' => '#10b981'];
                    $statusOffset = 0;
                @endphp
                <div class="relative w-44 h-44">
                    <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                        @foreach(($orderStatusCounts ?? []) as $status => $count)
                            @php
                                $pct = $count / $statusTotal;
                                $dashLen = $pct * $circumference;
                                $gapLen = $circumference - $dashLen;
                                $sColor = $statusColors[$status] ?? '#9ca3af';
                            @endphp
                            @if($count > 0)
                            <circle cx="18" cy="18" r="14" fill="none" stroke="{{ $sColor }}" stroke-width="4"
                                    stroke-dasharray="{{ round($dashLen, 1) }} {{ round($gapLen, 1) }}" stroke-dashoffset="{{ round(-$statusOffset, 1) }}" stroke-linecap="round"/>
                            @endif
                            @php $statusOffset += $dashLen; @endphp
                        @endforeach
                    </svg>
                </div>
                <div class="space-y-4">
                    @foreach(($orderStatusCounts ?? []) as $status => $count)
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $statusColors[$status] ?? '#9ca3af' }}"></span>
                        <div>
                            <p class="text-sm text-gray-500">{{ $status }}</p>
                            <p class="font-bold text-gray-900">{{ $count }} {{ Str::plural('Order', $count) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Top Selling Products Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="p-6 pb-4">
            <h3 class="text-lg font-bold text-gray-900">Top Selling Products</h3>
            <p class="text-sm text-gray-500 mt-1">Best performing products this month</p>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Units Sold</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Revenue</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Growth</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trend</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($topProducts ?? [] as $index => $product)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-bold text-gray-400">{{ $index + 1 }}</td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $product['name'] }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ number_format($product['sold']) }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">₱{{ number_format($product['revenue']) }}</td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-medium text-green-600">—</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-end gap-0.5 h-6">
                            @php $sparkline = [30, 45, 55, 50, 65, 60, 75]; @endphp
                            @foreach($sparkline as $val)
                            <div class="w-1 rounded-t-sm bg-emerald-400" style="height: {{ $val }}%"></div>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">No product sales data yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- Weekly Production & Worker Efficiency Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Weekly Production Output --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Weekly Production Output</h3>
            <div class="flex items-end justify-between gap-3 h-64 px-2">
                @php
                    $maxProd = max(array_values($prodDays ?? [0])) ?: 1;
                @endphp
                @foreach($prodDays ?? [] as $day => $val)
                <div class="flex flex-col items-center gap-2 flex-1">
                    <div class="w-full flex flex-col items-center justify-end h-52">
                        <div class="w-8 bg-emerald-500 rounded-t-md transition-all duration-500" style="height: {{ $maxProd > 0 ? ($val/$maxProd)*100 : 0 }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $day }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Sales by Customer --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Top Customers by Revenue</h3>
            <div class="space-y-5">
                @php
                    $maxSpent = collect($topCustomers ?? [])->max('spent') ?: 1;
                @endphp
                @forelse($topCustomers ?? [] as $customer)
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <div>
                            <span class="text-sm font-medium text-gray-700">{{ $customer['name'] }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ $customer['orders'] }} orders</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">₱{{ number_format($customer['spent']) }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ round(($customer['spent'] / $maxSpent) * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">No customer data yet</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Worker Efficiency --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Worker Efficiency</h3>
        <div class="space-y-5">
            @php
                $wColors = ['bg-emerald-500', 'bg-amber-500', 'bg-blue-500', 'bg-purple-500', 'bg-green-500', 'bg-red-500', 'bg-pink-500'];
            @endphp
            @forelse($workerEfficiency ?? [] as $wi => $worker)
            <div class="flex items-center gap-4">
                <div class="w-9 h-9 rounded-full {{ $wColors[$wi % count($wColors)] }} flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                    {{ $worker['initial'] }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $worker['name'] }}</span>
                        <span class="text-sm text-gray-500">{{ $worker['load'] }}% Completion</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $worker['load'] }}%"></div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">No employee data yet</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
