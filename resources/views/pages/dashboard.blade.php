@extends('partials.app', ['title' => 'Dashboard - Canson', 'activePage' => 'dashboard'])

@push('styles')
    @vite('resources/css/pages/dashboard.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/dashboard.js')
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
<div class="dashboard-page">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Overview</h2>
            <p class="text-gray-500 mt-1">Welcome back, here's what's happening today.</p>
        </div>
        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg text-xs md:text-sm font-medium transition-colors">
            Generate Report
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Orders --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                 <div class="w-8 h-8 xl:w-11 xl:h-11 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs xl:text-sm text-gray-500 text-end">Total Orders</p>
                    <p class="text-2xl xl:text-3xl font-bold text-gray-900 mt-1 text-end">{{ $totalOrders ?? 0 }}</p>
                    <p class="text-[0.6rem] xl:text-xs {{ ($ordersPctChange ?? 0) >= 0 ? 'text-green-500' : 'text-red-500' }} mt-1">{{ ($ordersPctChange ?? 0) >= 0 ? '+' : '' }}{{ $ordersPctChange ?? 0 }}% from last month</p>
                </div>
            </div>
        </div>

        {{-- Total Sales Revenue --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div class="w-8 h-8 xl:w-11 xl:h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs xl:text-sm text-gray-500 text-end">Total Sales</p>
                    <p class="text-2xl xl:text-3xl font-bold text-gray-900 mt-1 text-end">₱{{ number_format($totalSales ?? 0) }}</p>
                    <p class="text-[0.6rem] xl:text-xs {{ ($salesPctChange ?? 0) >= 0 ? 'text-green-500' : 'text-red-500' }} mt-1">{{ ($salesPctChange ?? 0) >= 0 ? '+' : '' }}{{ $salesPctChange ?? 0 }}% from last month</p>
                </div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                    <div class="w-8 h-8 xl:w-11 xl:h-11 rounded-xl bg-orange-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs xl:text-sm text-gray-500 text-end">Pending</p>
                    <p class="text-2xl xl:text-3xl font-bold text-gray-900 mt-1 text-end">{{ $pendingCount ?? 0 }}</p>
                    <p class="text-[0.6rem] xl:text-xs text-gray-400 mt-1">Requires attention</p>
                </div>
            </div>
        </div>

        {{-- Today's Sales --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div class="w-8 h-8 xl:w-11 xl:h-11 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs xl:text-sm text-gray-500 text-end">Today's Sales</p>
                    <p class="text-2xl xl:text-3xl font-bold text-gray-900 mt-1 text-end">₱{{ number_format($todaySales ?? 0) }}</p>
                    <p class="text-[0.6rem] xl:text-xs {{ ($todayPctChange ?? 0) >= 0 ? 'text-green-500' : 'text-red-500' }} mt-1">{{ ($todayPctChange ?? 0) >= 0 ? '+' : '' }}{{ $todayPctChange ?? 0 }}% vs yesterday</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Trend Row --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Sales Overview</h3>
                <p class="text-xs text-gray-400 mt-0.5">Weekly revenue trend</p>
            </div>
            <div class="flex items-center gap-2">
                <button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200">Weekly</button>
                <button class="px-3 py-1.5 text-xs font-medium rounded-lg text-gray-500 hover:bg-gray-50 border border-gray-200">Monthly</button>
                <button class="px-3 py-1.5 text-xs font-medium rounded-lg text-gray-500 hover:bg-gray-50 border border-gray-200">Yearly</button>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Sales Area Chart --}}
            <div class="lg:col-span-2">
                @php
                    $maxSale = max(array_column($salesDays ?? [], 'amount')) ?: 1;
                    $chartW = 600; $chartH = 200; $padTop = 10; $padBot = 10;
                    $usableH = $chartH - $padTop - $padBot;
                    $dayKeys = array_keys($salesDays ?? []);
                    $points = [];
                    foreach (array_values($salesDays ?? []) as $i => $d) {
                        $x = count($salesDays) > 1 ? round($i * ($chartW / (count($salesDays) - 1))) : $chartW / 2;
                        $y = round($padTop + $usableH - ($d['amount'] / $maxSale) * $usableH);
                        $points[] = ['x' => $x, 'y' => $y, 'amount' => $d['amount'], 'orders' => $d['orders']];
                    }
                    $linePoints = implode(' ', array_map(fn($p) => $p['x'].','.$p['y'], $points));
                    $areaPoints = "0,{$chartH} " . $linePoints . " {$chartW},{$chartH}";
                @endphp
                <div class="relative mt-4">
                    <svg viewBox="0 0 600 200" class="w-full h-auto" style="overflow: visible;" preserveAspectRatio="xMidYMid meet">
                        <defs>
                            <linearGradient id="salesGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.02"/>
                            </linearGradient>
                        </defs>
                        {{-- Grid lines --}}
                        @for($i = 0; $i < 5; $i++)
                            @php $gy = $padTop + ($usableH / 4) * $i; @endphp
                            <line x1="0" y1="{{ $gy }}" x2="{{ $chartW }}" y2="{{ $gy }}" stroke="#f3f4f6" stroke-width="1" stroke-dasharray="4 4"/>
                        @endfor
                        {{-- Area fill --}}
                        <polygon points="{{ $areaPoints }}" fill="url(#salesGrad)"/>
                        {{-- Line --}}
                        <polyline points="{{ $linePoints }}" fill="none" stroke="#10b981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        {{-- Data points with tooltips --}}
                        @foreach($points as $i => $p)
                            @php
                                $ttW = 68; $ttH = 26;
                                $ttX = max($ttW/2, min($p['x'], $chartW - $ttW/2));
                                $ttY = max($p['y'] - 30, 2);
                                $arrowX = $p['x'] - $ttX;
                            @endphp
                            <g class="chart-dot-group" style="cursor: pointer;">
                                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="14" fill="transparent" class="chart-hit-area"/>
                                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3" fill="white" stroke="#10b981" stroke-width="2" class="chart-dot"/>
                                <line x1="{{ $p['x'] }}" y1="{{ $p['y'] }}" x2="{{ $p['x'] }}" y2="{{ $chartH }}" stroke="#10b981" stroke-width="1" stroke-dasharray="3 3" class="chart-guide-line" opacity="0"/>
                                <g class="chart-tooltip" opacity="0" transform="translate({{ $ttX }}, {{ $ttY }})">
                                    <rect x="{{ -$ttW/2 }}" y="-2" width="{{ $ttW }}" height="{{ $ttH }}" rx="6" fill="#1f2937" opacity="0.95"/>
                                    <polygon points="{{ $arrowX - 4 }},{{ $ttH - 2 }} {{ $arrowX + 4 }},{{ $ttH - 2 }} {{ $arrowX }},{{ $ttH + 3 }}" fill="#1f2937" opacity="0.95"/>
                                    <text x="0" y="8" text-anchor="middle" fill="white" font-size="8" font-weight="600">₱{{ number_format($p['amount']) }}</text>
                                    <text x="0" y="17" text-anchor="middle" fill="#9ca3af" font-size="6.5">{{ $p['orders'] }} orders</text>
                                </g>
                            </g>
                        @endforeach
                    </svg>
                    {{-- X-axis labels --}}
                    <div class="flex justify-between px-0 mt-2">
                        @foreach($dayKeys as $day)
                            <span class="text-xs text-gray-400 font-medium">{{ $day }}</span>
                        @endforeach
                    </div>
                    {{-- Y-axis labels --}}
                    <div class="absolute left-1 top-4 h-[calc(100%-2.5rem)] flex flex-col justify-between pointer-events-none">
                        @php
                            $yStep = $maxSale / 4;
                            $yLabels = [];
                            for ($i = 4; $i >= 0; $i--) {
                                $val = $yStep * $i;
                                $yLabels[] = $val >= 1000 ? '₱' . round($val / 1000) . 'K' : '₱' . (int)$val;
                            }
                        @endphp
                        @foreach($yLabels as $lbl)
                            <span class="text-[10px] text-gray-300 font-medium leading-none">{{ $lbl }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            {{-- Quick Sales Summary --}}
            <div class="space-y-4">
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-4 border border-emerald-100">
                    <p class="text-xs text-gray-500 mb-1">This Week's Revenue</p>
                    <p class="text-xl font-bold text-gray-900">₱{{ number_format(array_sum(array_column($salesDays ?? [], 'amount'))) }}</p>
                    <div class="flex items-center gap-1 mt-2">
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-semibold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                            23.5%
                        </span>
                        <span class="text-[10px] text-gray-400">vs last week</span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                    <p class="text-xs text-gray-500 mb-1">Average Order Value</p>
                    @php
                        $weeklyOrders = array_sum(array_column($salesDays ?? [], 'orders'));
                        $weeklyRevenue = array_sum(array_column($salesDays ?? [], 'amount'));
                        $avgOrderVal = $weeklyOrders > 0 ? $weeklyRevenue / $weeklyOrders : 0;
                    @endphp
                    <p class="text-xl font-bold text-gray-900">₱{{ number_format($avgOrderVal) }}</p>
                    <div class="flex items-center gap-1 mt-2">
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-semibold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                            5.2%
                        </span>
                        <span class="text-[10px] text-gray-400">vs last week</span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-fuchsia-50 rounded-xl p-4 border border-purple-100">
                    <p class="text-xs text-gray-500 mb-1">Total Transactions</p>
                    <p class="text-xl font-bold text-gray-900">{{ $weeklyOrders }}</p>
                    <div class="flex items-center gap-1 mt-2">
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-700 text-[10px] font-semibold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                            +11 orders
                        </span>
                        <span class="text-[10px] text-gray-400">vs last week</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Weekly Production Output --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-lg font-bold text-gray-900">Weekly Production</h3>
                <span class="text-xs text-gray-400">Units produced</span>
            </div>
            @php
                $prodMax = max(array_values($prodDays ?? [])) ?: 1;
                $pW = 500; $pH = 180; $pPadTop = 10; $pPadBot = 10;
                $pUsableH = $pH - $pPadTop - $pPadBot;
                $pKeys = array_keys($prodDays ?? []);
                $pVals = array_values($prodDays ?? []);
                $pPoints = [];
                foreach ($pVals as $i => $v) {
                    $px = round($i * ($pW / (count($pVals) - 1)));
                    $py = round($pPadTop + $pUsableH - ($v / $prodMax) * $pUsableH);
                    $pPoints[] = ['x' => $px, 'y' => $py, 'val' => $v];
                }
                $pLine = implode(' ', array_map(fn($p) => $p['x'].','.$p['y'], $pPoints));
                $pArea = "0,{$pH} " . $pLine . " {$pW},{$pH}";
            @endphp
            <div class="relative">
                <svg viewBox="0 0 500 180" class="w-full h-auto" style="overflow: visible;" preserveAspectRatio="xMidYMid meet">
                    <defs>
                        <linearGradient id="prodGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.3"/>
                            <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0.02"/>
                        </linearGradient>
                    </defs>
                    @for($i = 0; $i < 4; $i++)
                        @php $gy = $pPadTop + ($pUsableH / 3) * $i; @endphp
                        <line x1="0" y1="{{ $gy }}" x2="{{ $pW }}" y2="{{ $gy }}" stroke="#f3f4f6" stroke-width="1" stroke-dasharray="4 4"/>
                    @endfor
                    <polygon points="{{ $pArea }}" fill="url(#prodGrad)"/>
                    <polyline points="{{ $pLine }}" fill="none" stroke="#8b5cf6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    @foreach($pPoints as $i => $p)
                        @php
                            $ptW = 56; $ptH = 20;
                            $ptX = max($ptW/2, min($p['x'], $pW - $ptW/2));
                            $ptY = max($p['y'] - 26, 2);
                            $ptArrowX = $p['x'] - $ptX;
                        @endphp
                        <g class="chart-dot-group" style="cursor: pointer;">
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="14" fill="transparent" class="chart-hit-area"/>
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3" fill="white" stroke="#8b5cf6" stroke-width="2" class="chart-dot"/>
                            <line x1="{{ $p['x'] }}" y1="{{ $p['y'] }}" x2="{{ $p['x'] }}" y2="{{ $pH }}" stroke="#8b5cf6" stroke-width="1" stroke-dasharray="3 3" class="chart-guide-line" opacity="0"/>
                            <g class="chart-tooltip" opacity="0" transform="translate({{ $ptX }}, {{ $ptY }})">
                                <rect x="{{ -$ptW/2 }}" y="-2" width="{{ $ptW }}" height="{{ $ptH }}" rx="5" fill="#1f2937" opacity="0.95"/>
                                <polygon points="{{ $ptArrowX - 3 }},{{ $ptH - 2 }} {{ $ptArrowX + 3 }},{{ $ptH - 2 }} {{ $ptArrowX }},{{ $ptH + 2 }}" fill="#1f2937" opacity="0.95"/>
                                <text x="0" y="11" text-anchor="middle" fill="white" font-size="7.5" font-weight="600">{{ $p['val'] }} units</text>
                            </g>
                        </g>
                    @endforeach
                </svg>
                <div class="flex justify-between mt-2">
                    @foreach($pKeys as $day)
                        <span class="text-xs text-gray-400 font-medium">{{ $day }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Order Status Distribution --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Order Status Distribution</h3>
            <div class="flex items-center justify-center gap-8">
                {{-- Donut Chart --}}
                <div class="relative w-44 h-44">
                    <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#3b82f6" stroke-width="4"
                                stroke-dasharray="35.2 52.8" stroke-dashoffset="0" stroke-linecap="round"/>
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#10b981" stroke-width="4"
                                stroke-dasharray="35.2 52.8" stroke-dashoffset="-35.2" stroke-linecap="round"/>
                        <circle cx="18" cy="18" r="14" fill="none" stroke="#f59e0b" stroke-width="4"
                                stroke-dasharray="17.6 70.4" stroke-dashoffset="-70.4" stroke-linecap="round"/>
                    </svg>
                </div>
                {{-- Legend --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <div>
                            <p class="text-sm text-gray-500">Pending</p>
                            <p class="font-bold text-gray-900">{{ $orderStatusCounts['Pending'] ?? 0 }} Orders</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                        <div>
                            <p class="text-sm text-gray-500">In Progress</p>
                            <p class="font-bold text-gray-900">{{ $orderStatusCounts['In-Progress'] ?? 0 }} Orders</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <div>
                            <p class="text-sm text-gray-500">Completed</p>
                            <p class="font-bold text-gray-900">{{ $orderStatusCounts['Completed'] ?? 0 }} Orders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Sales & Top Products --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        {{-- Recent Sales Table --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between p-6 pb-4">
                <h3 class="text-lg font-bold text-gray-900">Recent Sales</h3>
                <a href="/sales" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">View All &rarr;</a>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Transaction</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentSales as $sale)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3.5">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $sale['id'] }}</p>
                                <p class="text-xs text-gray-400">{{ $sale['date'] }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-sm text-gray-600">{{ $sale['customer'] }}</td>
                        <td class="px-6 py-3.5 text-sm font-semibold text-gray-900">₱{{ number_format($sale['amount']) }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sale['statusColor'] }}">
                                {{ $sale['status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Top Selling Products --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top Products</h3>
            <div class="space-y-4">
                @foreach($topProducts as $index => $product)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-xs font-bold text-emerald-600 flex-shrink-0">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $product['name'] }}</p>
                        <p class="text-xs text-gray-400">{{ $product['sold'] }} units sold</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-semibold text-gray-900">₱{{ number_format($product['revenue']) }}</p>
                    </div>
                </div>
                @if(!$loop->last)
                <div class="border-t border-gray-100"></div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
