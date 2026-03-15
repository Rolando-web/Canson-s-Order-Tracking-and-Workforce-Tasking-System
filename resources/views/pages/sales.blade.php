@extends('partials.app', ['title' => 'Sales - Canson', 'activePage' => 'sales'])

@push('styles')
    @vite('resources/css/pages/sales.css')
@endpush

@section('content')
<div class="sales-page">

    {{-- ── Page Header ── --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Sales</h2>
            <p class="text-gray-500 mt-1">Automatically tracked from orders for better decision-making.</p>
        </div>
        <div class="flex items-center flex-col sm:flex-row gap-2">
            {{-- Print Report --}}
            <a href="{{ route('sales.print') }}?{{ http_build_query(array_filter(request()->only(['search','status','date']))) }}"
               target="_blank"
               class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-[10px] sm:text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                </svg>
                Print Report
            </a>
            {{-- Export CSV --}}
            <a href="{{ route('sales.exportCsv') }}?{{ http_build_query(array_filter(request()->only(['search','status','date']))) }}"
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-[10px] sm:text-sm font-medium transition-colors flex items-center gap-2 shadow-sm shadow-emerald-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Today's Sales --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if(($revenuePctChange ?? 0) >= 0)
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-green-50 text-green-600 text-[10px] font-semibold">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                    {{ $revenuePctChange ?? 0 }}%
                </span>
                @else
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-[10px] font-semibold">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25"/></svg>
                    {{ abs($revenuePctChange ?? 0) }}%
                </span>
                @endif
            </div>
            <p class="text-gray-500 text-xs">Today's Sales</p>
            <p class="text-gray-900 text-2xl font-bold mt-0.5">₱{{ number_format($todaySalesAmount ?? 0) }}</p>
            <p class="text-gray-400 text-[10px] mt-1">from completed orders</p>
        </div>

        {{-- This Week --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                </div>
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[10px] font-semibold">Weekly</span>
            </div>
            @php
                $weeklyTotal = array_sum(array_column($salesTrend ?? [], 'amount'));
                $weeklyCount = array_sum(array_column($salesTrend ?? [], 'orders'));
            @endphp
            <p class="text-gray-500 text-xs">This Week</p>
            <p class="text-2xl font-bold text-gray-900 mt-0.5">₱{{ number_format($weeklyTotal ?? 0) }}</p>
            <p class="text-gray-400 text-[10px] mt-1">{{ $weeklyCount ?? 0 }} transactions</p>
        </div>

        {{-- This Month --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941"/>
                    </svg>
                </div>
                @if(($revenuePctChange ?? 0) >= 0)
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-green-50 text-green-600 text-[10px] font-semibold">+{{ $revenuePctChange ?? 0 }}%</span>
                @else
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-[10px] font-semibold">{{ $revenuePctChange ?? 0 }}%</span>
                @endif
            </div>
            <p class="text-gray-500 text-xs">This Month</p>
            <p class="text-2xl font-bold text-gray-900 mt-0.5">₱{{ number_format($thisMonthRevenue ?? 0) }}</p>
            <p class="text-gray-400 text-[10px] mt-1">{{ $thisMonthTransactions ?? 0 }} transactions</p>
        </div>

        {{-- Avg Order Value --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full font-semibold">All Time</span>
            </div>
            <p class="text-gray-500 text-xs">Avg. Order Value</p>
            <p class="text-2xl font-bold text-gray-900 mt-0.5">₱{{ number_format($avgOrderValue ?? 0) }}</p>
            <p class="text-gray-400 text-[10px] mt-1">per transaction</p>
        </div>
    </div>

    {{-- ── Sales Trend Chart ── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Sales Trend</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    @if($period === 'weekly') Weekly revenue · Last 8 weeks
                    @elseif($period === 'monthly') Monthly revenue · Last 12 months
                    @else Daily revenue · Last 7 days
                    @endif
                </p>
            </div>
            <div class="flex items-center flex-col sm:flex-row gap-2">
                <a href="{{ request()->fullUrlWithQuery(['period' => 'daily']) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors {{ $period === 'daily' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'text-gray-500 hover:bg-gray-50 border-gray-200' }}">
                    Daily
                </a>
                <a href="{{ request()->fullUrlWithQuery(['period' => 'weekly']) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors {{ $period === 'weekly' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'text-gray-500 hover:bg-gray-50 border-gray-200' }}">
                    Weekly
                </a>
                <a href="{{ request()->fullUrlWithQuery(['period' => 'monthly']) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors {{ $period === 'monthly' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'text-gray-500 hover:bg-gray-50 border-gray-200' }}">
                    Monthly
                </a>
            </div>
        </div>
        @php
            $dailySales  = array_values(array_map(fn($d) => $d['amount'], $salesTrend ?? []));
            $dailyLabels = array_keys($salesTrend ?? []);
            if (empty($dailySales)) { $dailySales = [0]; $dailyLabels = ['N/A']; }
            $maxDaily    = max($dailySales) ?: 1;
            $dW = 660; $dH = 200; $dPadTop = 10; $dPadBot = 10;
            $dUsableH = $dH - $dPadTop - $dPadBot;
            $dPoints  = [];
            $numPoints = count($dailySales);
            foreach ($dailySales as $i => $amt) {
                $dx = $numPoints > 1 ? round($i * ($dW / ($numPoints - 1))) : $dW / 2;
                $dy = round($dPadTop + $dUsableH - ($amt / $maxDaily) * $dUsableH);
                $dPoints[] = ['x' => $dx, 'y' => $dy, 'amount' => $amt, 'label' => $dailyLabels[$i] ?? ''];
            }
            $dLine = implode(' ', array_map(fn($p) => $p['x'].','.$p['y'], $dPoints));
            $dArea = "0,{$dH} " . $dLine . " {$dW},{$dH}";
        @endphp
        <div class="relative">
            <svg viewBox="0 0 660 200" class="w-full h-auto" style="overflow: visible;" preserveAspectRatio="xMidYMid meet">
                <defs>
                    <linearGradient id="dailyGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#10b981" stop-opacity="0.3"/>
                        <stop offset="50%" stop-color="#10b981" stop-opacity="0.08"/>
                        <stop offset="100%" stop-color="#10b981" stop-opacity="0.01"/>
                    </linearGradient>
                </defs>
                @for($i = 0; $i < 5; $i++)
                    @php $gy = $dPadTop + ($dUsableH / 4) * $i; @endphp
                    <line x1="0" y1="{{ $gy }}" x2="{{ $dW }}" y2="{{ $gy }}" stroke="#f3f4f6" stroke-width="1"/>
                @endfor
                <polygon points="{{ $dArea }}" fill="url(#dailyGrad)"/>
                <polyline points="{{ $dLine }}" fill="none" stroke="#10b981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                @foreach($dPoints as $i => $p)
                    @php
                        $stW = 84; $stH = 20;
                        $stX = max((int)($stW/2), min($p['x'], $dW - (int)($stW/2)));
                        $stY = max($p['y'] - 26, 2);
                        $stArrowX = $p['x'] - $stX;
                    @endphp
                    <g class="chart-dot-group" style="cursor: pointer;">
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="12" fill="transparent" class="chart-hit-area"/>
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3" fill="white" stroke="#10b981" stroke-width="1.5" class="chart-dot"/>
                        <line x1="{{ $p['x'] }}" y1="{{ $p['y'] }}" x2="{{ $p['x'] }}" y2="{{ $dH }}" stroke="#10b981" stroke-width="1" stroke-dasharray="3 3" class="chart-guide-line" opacity="0"/>
                        <g class="chart-tooltip" opacity="0" transform="translate({{ $stX }}, {{ $stY }})">
                            <rect x="{{ -(int)($stW/2) }}" y="-2" width="{{ $stW }}" height="{{ $stH }}" rx="5" fill="#1f2937" opacity="0.95"/>
                            <polygon points="{{ $stArrowX - 3 }},{{ $stH - 2 }} {{ $stArrowX + 3 }},{{ $stH - 2 }} {{ $stArrowX }},{{ $stH + 2 }}" fill="#1f2937" opacity="0.95"/>
                            <text x="0" y="11" text-anchor="middle" fill="white" font-size="6.5" font-weight="600">{{ $p['label'] }}: ₱{{ number_format($p['amount']) }}</text>
                        </g>
                    </g>
                @endforeach
            </svg>
            <div class="flex justify-between mt-2">
                @foreach($dPoints as $i => $p)
                    @if($i === 0 || $i === count($dPoints) - 1 || ($numPoints > 4 && $i % max(1, intval($numPoints / 4)) === 0))
                        <span class="text-[10px] text-gray-400">{{ $p['label'] }}</span>
                    @else
                        <span class="text-[10px] text-transparent select-none">.</span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Sales Records Section Indicator ── --}}
    <div class="mb-3 flex items-center gap-3">
        <div class="flex items-center gap-2">
            <div class="w-1 h-6 bg-emerald-500 rounded-full"></div>
            <h3 class="text-base font-bold text-gray-800">Sales Records</h3>
        </div>
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full font-medium">
            {{ $salesPaginated->total() }} total records
        </span>
    </div>

    {{-- ── Filters & Search (Form) ── --}}
    <form method="GET" action="{{ route('sales') }}" class="bg-white rounded-xl border border-gray-200 p-4 mb-0">
        <input type="hidden" name="period" value="{{ $period }}">
        <div class="flex flex-wrap items-center gap-3">
            <div class="min-w-[200px] relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by transaction ID, customer name..."
                       class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-gray-50">
            </div>
            <select name="status" class="sm:flex-0 flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50">
                <option value="all"              {{ request('status','all') === 'all'              ? 'selected' : '' }}>All Status</option>
                <option value="Pending"           {{ request('status') === 'Pending'               ? 'selected' : '' }}>Pending</option>
                <option value="In-Progress"       {{ request('status') === 'In-Progress'           ? 'selected' : '' }}>In-Progress</option>
                <option value="Ready for Delivery"{{ request('status') === 'Ready for Delivery'   ? 'selected' : '' }}>Ready for Delivery</option>
                <option value="Delivered"         {{ request('status') === 'Delivered'             ? 'selected' : '' }}>Delivered</option>
                <option value="Completed"         {{ request('status') === 'Completed'             ? 'selected' : '' }}>Completed</option>
            </select>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="sm:flex-0 flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50">
                <button type="submit" class="bg-emerald-600 sm:flex-0 flex-1 justify-center hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17l-5-5m0 0l5-5m-5 5h12a2 2 0 000-4H9"/>
                </svg>
                Filter
            </button>
            @if(request()->hasAny(['search','status','date']))
            <a href="{{ route('sales') }}?period={{ $period }}"
               class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Clear
            </a>
            @endif
        </div>
    </form>

    {{-- ── Sales Table ── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[800px]">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Transaction</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($salesPaginated as $sale)
                <tr class="hover:bg-emerald-50/30 transition-colors">
                    <td class="px-5 py-4">
                        <p class="text-sm font-semibold text-gray-900">{{ $sale['id'] }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $sale['date'] }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-bold text-emerald-700 flex-shrink-0">
                                {{ strtoupper(substr($sale['customer'], 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $sale['customer'] }}</p>
                                <p class="text-[11px] text-gray-400">{{ $sale['contact'] ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm text-gray-700">{{ Str::limit($sale['items'], 30) }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ number_format($sale['qty']) }} units</p>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <p class="text-sm font-bold text-gray-900">₱{{ number_format($sale['amount']) }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $sale['statusBadge'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sale['statusColor'] }}"></span>
                            {{ $sale['status'] }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm text-gray-600">{{ $sale['date'] }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center">
                            <button onclick="openSaleModal({{ json_encode($sale) }})"
                                    class="w-7 h-7 rounded-lg hover:bg-emerald-100 flex items-center justify-center transition-colors group"
                                    title="View Details">
                                <svg class="w-4 h-4 text-gray-500 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m16.5 0h-16.5"/>
                        </svg>
                        <p class="text-sm font-medium">No sales records found</p>
                        <p class="text-xs mt-1">Try adjusting your search or filters</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- Pagination --}}
        @if($salesPaginated->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 bg-gray-50/50">
            <p class="text-xs text-gray-500">
                Showing <span class="font-semibold text-gray-700">{{ $salesPaginated->firstItem() }}-{{ $salesPaginated->lastItem() }}</span>
                of <span class="font-semibold text-gray-700">{{ $salesPaginated->total() }}</span> records
            </p>
            <div class="flex items-center gap-1">
                @if($salesPaginated->onFirstPage())
                <button class="px-3 py-1.5 text-xs text-gray-400 border border-gray-200 rounded-lg cursor-not-allowed bg-white" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </button>
                @else
                <a href="{{ $salesPaginated->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 bg-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </a>
                @endif
                @foreach($salesPaginated->getUrlRange(1, $salesPaginated->lastPage()) as $page => $url)
                    @if($page == $salesPaginated->currentPage())
                    <button class="w-8 h-8 text-xs font-semibold text-white bg-emerald-600 rounded-lg flex items-center justify-center">{{ $page }}</button>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 text-xs text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 bg-white flex items-center justify-center">{{ $page }}</a>
                    @endif
                @endforeach
                @if($salesPaginated->hasMorePages())
                <a href="{{ $salesPaginated->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 bg-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                @else
                <button class="px-3 py-1.5 text-xs text-gray-400 border border-gray-200 rounded-lg cursor-not-allowed bg-white" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ── View Details Modal ── --}}
@include('pages.Modals.sale-detail-modal')

@push('scripts')
@vite('resources/js/pages/sales.js')
@endpush
@endsection