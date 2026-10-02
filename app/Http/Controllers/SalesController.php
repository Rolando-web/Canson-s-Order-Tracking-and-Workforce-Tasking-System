<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderPhaseItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Support\DateBucket;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        // â”€â”€ Headline totals â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // The month figures used to be four separate whereYear()+whereMonth()
        // pairs. Both months now come out of one range scan. The range spans
        // exactly two consecutive months, so the month number alone identifies
        // which is which, and both forms now pin the year explicitly.
        $monthTotals = Order::query()
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->selectRaw(
                DateBucket::monthNumber('created_at').' as month_key, '
                .'COUNT(*) as period_orders, COALESCE(SUM(total_amount), 0) as period_total'
            )
            ->groupBy('month_key')
            ->get()
            ->keyBy('month_key');

        $thisMonthRow = $monthTotals[now()->month] ?? null;
        $lastMonthRow = $monthTotals[now()->subMonth()->month] ?? null;

        $thisMonthRevenue      = (float) ($thisMonthRow->period_total ?? 0);
        $thisMonthTransactions = (int) ($thisMonthRow->period_orders ?? 0);

        $lastMonthRevenue = (float) ($lastMonthRow->period_total ?? 0);
        $revenuePctChange = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        $grandTotals = Order::query()
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as sales_total')
            ->first();

        $totalRevenue      = (float) $grandTotals->sales_total;
        $totalTransactions = (int) $grandTotals->order_count;
        $avgOrderValue     = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // â”€â”€ Trend series: one grouped query instead of up to 24 â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $period     = $request->input('period', 'daily');
        $salesTrend = $this->buildSalesTrend($period);

        $query = Order::with('phases.items')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('order_number', 'like', "%{$s}%"));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $query->filterByDay($request->input('date'));

        $salesPaginated = $query->paginate(10)->withQueryString();

        $sales = $salesPaginated->getCollection()->map(function ($order) {
            $allItems = $order->phases->flatMap->items;
            $uniqueNames = $allItems->pluck('name')->unique()->implode(', ') ?: 'N/A';

            return [
                'id'          => $order->order_number,
                'db_id'       => $order->Order_Id,
                'customer'    => $order->customer_name,
                'contact'     => $order->contact_number,
                'address'     => $order->delivery_address,
                'items'       => $uniqueNames,
                'qty'         => $allItems->sum('base_qty'),
                'amount'      => $order->total_amount,
                'status'      => $order->status,
                'statusColor' => match($order->status) {
                    'Completed'          => 'bg-green-500',
                    'In-Progress'        => 'bg-emerald-500',
                    'Ready for Delivery' => 'bg-blue-500',
                    'Delivered'          => 'bg-teal-500',
                    default              => 'bg-gray-400',
                },
                'statusBadge' => match($order->status) {
                    'Completed'          => 'bg-green-50 text-green-700',
                    'In-Progress'        => 'bg-emerald-50 text-emerald-700',
                    'Ready for Delivery' => 'bg-blue-50 text-blue-700',
                    'Delivered'          => 'bg-teal-50 text-teal-700',
                    default              => 'bg-gray-100 text-gray-600',
                },
                'date'        => $order->created_at->format('M d, Y'),
                'notes'       => $order->notes,
                'priority'    => $order->priority,
                'order_items' => $order->phases->first()?->items->map(fn($i) => [
                    'name'     => $i->name,
                    'qty'      => $i->base_qty,
                    'price'    => $i->unit_price,
                    'subtotal' => $i->subtotal,
                ])->toArray() ?? [],
            ];
        });

        $salesPaginated->setCollection($sales);

        $topCategory = OrderPhaseItem::select('product_id', DB::raw('SUM(subtotal) as total'))
            ->groupBy('product_id')->orderByDesc('total')->first();
        $topCategoryName = $topCategory && $topCategory->product
            ? $topCategory->product->category : 'N/A';

        $todaySalesAmount = Order::forDay(now())->sum('total_amount');

        return view('pages.sales', compact(
            'totalRevenue', 'totalTransactions', 'avgOrderValue',
            'thisMonthRevenue', 'thisMonthTransactions',
            'revenuePctChange', 'salesTrend', 'salesPaginated',
            'topCategoryName', 'todaySalesAmount', 'period'
        ));
    }

    /**
     * Build the trend series for the sales page.
     *
     * The original ran one sum() and one count() per bucket inside a loop:
     * 8 buckets for weekly, 12 for monthly, 7 for daily. That is up to 24
     * queries, each one a non-sargable full table scan because of whereDate()
     * and whereYear()+whereMonth(). Every bucket is now derived from a single
     * grouped query over one range, and each label is pre-seeded to zero so
     * the chart always renders a complete axis.
     *
     * @return array<string, array{amount: float, orders: int}>
     */
    private function buildSalesTrend(string $period): array
    {
        if ($period === 'weekly') {
            // 8 weeks, Monday-aligned, oldest first.
            $weekStarts = [];
            for ($i = 7; $i >= 0; $i--) {
                $weekStarts[] = now()->startOfWeek()->subWeeks($i);
            }

            $start = $weekStarts[0]->copy()->startOfDay();
            $end   = $weekStarts[7]->copy()->endOfWeek();

            // Bucketed by DAY, then folded into weeks in PHP.
            //
            // Bucketing by week in SQL would need WEEK(), whose week-start day
            // and week-numbering differ between MySQL and PostgreSQL, and this
            // app runs on both. Summing seven daily buckets is portable and
            // costs nothing: the query already returned every row in range.
            $daily = $this->groupOrdersBy($start, $end, DateBucket::day('created_at'));

            $series = [];
            foreach ($weekStarts as $weekStart) {
                $amount = 0.0;
                $orders = 0;

                for ($day = 0; $day < 7; $day++) {
                    $key = $weekStart->copy()->addDays($day)->toDateString();
                    $amount += (float) ($daily[$key]->total ?? 0);
                    $orders += (int) ($daily[$key]->orders ?? 0);
                }

                $series[$weekStart->format('M d')] = [
                    'amount' => $amount,
                    'orders' => $orders,
                ];
            }

            return $series;
        }

        if ($period === 'monthly') {
            $labels = [];
            for ($i = 11; $i >= 0; $i--) {
                $labels[] = now()->subMonths($i);
            }
            $start = $labels[0]->copy()->startOfMonth();
            $end   = $labels[11]->copy()->endOfMonth();

            $buckets = $this->groupOrdersBy($start, $end, DateBucket::month('created_at'));

            $series = [];
            foreach ($labels as $month) {
                $key = $month->format('Y-m');
                $series[$month->format('M Y')] = [
                    'amount' => (float) ($buckets[$key]->total ?? 0),
                    'orders' => (int) ($buckets[$key]->orders ?? 0),
                ];
            }

            return $series;
        }

        // daily: the last 7 days including today
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = now()->subDays($i);
        }
        $start = $labels[0]->copy()->startOfDay();
        $end   = $labels[6]->copy()->endOfDay();

        $buckets = $this->groupOrdersBy($start, $end, DateBucket::day('created_at'));

        $series = [];
        foreach ($labels as $day) {
            $key = $day->toDateString();
            $series[$day->format('M d')] = [
                'amount' => (float) ($buckets[$key]->total ?? 0),
                'orders' => (int) ($buckets[$key]->orders ?? 0),
            ];
        }

        return $series;
    }

    /**
     * Aggregate orders into time buckets with one range scan.
     *
     * The range on the raw timestamp is what the index serves. The bucket
     * expression is only applied to the rows inside that already-narrow range,
     * so it never triggers a full scan.
     *
     * $bucketExpression must be a raw SQL expression producing the exact key
     * the caller will look up by. Both constants below are deliberately
     * portable across MySQL and PostgreSQL, since the app runs on MySQL
     * locally and PostgreSQL in production.
     *
     * @return \Illuminate\Support\Collection<string, object> keyed by bucket
     */
    private function groupOrdersBy($start, $end, string $bucketExpression)
    {
        return Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw(
                $bucketExpression.' as bucket, '
                .'COALESCE(SUM(total_amount), 0) as total, COUNT(*) as orders'
            )
            ->groupBy('bucket')
            ->get()
            ->keyBy(fn ($row) => (string) $row->bucket);
    }

    public function exportCsv(Request $request)
    {
        $query = Order::with('phases.items')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('order_number', 'like', "%{$s}%"));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        $query->filterByDay($request->input('date'));

        $orders   = $query->get();
        $filename = 'sales_report_' . now()->format('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Transaction ID', 'Customer', 'Contact', 'Items', 'Qty', 'Amount (PHP)', 'Status', 'Date']);

            foreach ($orders as $order) {
                $allItems = $order->phases->flatMap->items;
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer_name,
                    $order->contact_number,
                    $allItems->pluck('name')->unique()->implode('; '),
                    $allItems->sum('base_qty'),
                    number_format($order->total_amount, 2),
                    $order->status,
                    $order->created_at->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function reportsIndex()
    {
        $grandTotals = Order::query()
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as sales_total')
            ->first();

        $totalRevenue = (float) $grandTotals->sales_total;
        $totalOrders  = (int) $grandTotals->order_count;

        $thisMonthRevenue = Order::forMonth(now())->sum('total_amount');
        $todaySales       = Order::forDay(now())->sum('total_amount');

        // 12 months of revenue in one grouped scan rather than twelve
        // whereYear()+whereMonth() pairs.
        $monthlyRows = Order::query()
            ->whereBetween('created_at', [
                now()->subMonths(11)->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->selectRaw(
                DateBucket::month('created_at').' as bucket, COALESCE(SUM(total_amount), 0) as month_total'
            )
            ->groupBy('bucket')
            ->get()
            ->keyBy(fn ($row) => (string) $row->bucket);

        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyRevenue[$date->format('M Y')] = (float) ($monthlyRows[$date->format('Y-m')]->month_total ?? 0);
        }

        // Five status counts become one grouped query.
        $statusRows = Order::query()
            ->select('status', DB::raw('COUNT(*) as status_count'))
            ->groupBy('status')
            ->pluck('status_count', 'status');

        $statusCounts = [
            'Pending'            => (int) ($statusRows['Pending'] ?? 0),
            'In-Progress'        => (int) ($statusRows['In-Progress'] ?? 0),
            'Ready for Delivery' => (int) ($statusRows['Ready for Delivery'] ?? 0),
            'Delivered'          => (int) ($statusRows['Delivered'] ?? 0),
            'Completed'          => (int) ($statusRows['Completed'] ?? 0),
        ];

        $topProducts = OrderPhaseItem::select('name', DB::raw('SUM(base_qty) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('name')->orderByDesc('total_revenue')->limit(5)->get();

        $recentOrders = Order::with('phases.items')->orderBy('created_at', 'desc')->limit(10)->get()->map(fn($o) => [
            'id'       => $o->order_number,
            'customer' => $o->customer_name,
            'items'    => $o->phases->flatMap->items->pluck('name')->unique()->implode(', ') ?: 'N/A',
            'amount'   => $o->total_amount,
            'status'   => $o->status,
            'date'     => $o->created_at->format('M d, Y'),
        ]);

        return view('pages.reports.index', compact(
            'totalRevenue', 'totalOrders', 'thisMonthRevenue', 'todaySales',
            'monthlyRevenue', 'statusCounts', 'topProducts', 'recentOrders'
        ));
    }

    public function printReport(Request $request)
    {
        $query = Order::with('phases.items')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('order_number', 'like', "%{$s}%"));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        $query->filterByDay($request->input('date'));

        $orders = $query->get()->map(fn($o) => [
            'id'       => $o->order_number,
            'customer' => $o->customer_name,
            'contact'  => $o->contact_number,
            'items'    => $o->phases->flatMap->items->pluck('name')->unique()->implode(', ') ?: 'N/A',
            'qty'      => $o->phases->flatMap->items->sum('base_qty'),
            'amount'   => $o->total_amount,
            'status'   => $o->status,
            'date'     => $o->created_at->format('M d, Y'),
        ]);

        $totalRevenue = $orders->sum('amount');
        $totalOrders  = $orders->count();
        $generatedAt  = now()->format('F d, Y h:i A');

        return view('pages.reports.sales-print', compact('orders', 'totalRevenue', 'totalOrders', 'generatedAt'));
    }
}