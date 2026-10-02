<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderPhaseItem;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Support\DateBucket;

class DashboardController extends Controller
{
    /**
     * Seconds a fully aggregated dashboard stays cached for the next viewer.
     *
     * This is the largest single lever on perceived speed for the common case,
     * because several staff members refreshing the dashboard at the same time
     * all share one result. It is deliberately short: the page still reflects
     * new orders within a minute, and dropping the cache is one number here.
     * See forgetDashboardCache() for the targeted invalidation path.
     */
    private const DASHBOARD_TTL = 60;

    /**
     * Forget the cached dashboard payload.
     *
     * Worth calling from the controllers that mutate what the dashboard
     * reports on, if the one minute of staleness is not acceptable. Not wired
     * up by default so the cache stays purely a read-path optimisation and
     * cannot be invalidated inconsistently.
     */
    public static function forgetDashboardCache(): void
    {
        Cache::forget('dashboard.payload');
    }

    public function index()
    {
        // Aggregates are computed once and memoised. A cache hit here means
        // the entire page below renders from memory instead of the database.
        $payload = Cache::remember('dashboard.payload', self::DASHBOARD_TTL, fn () => $this->buildDashboard());

        return view('pages.dashboard', $payload);
    }

    /**
     * Collect every figure the dashboard renders.
     *
     * Previously this method issued roughly 35 queries, 13 of which wrapped
     * created_at in DATE() or MONTH(). Those predicates compile to
     * `DATE(created_at) = '...'`, which is non-sargable: no index can ever
     * satisfy it, so MySQL full-scanned the orders table for every one of
     * them. The rewrite keeps the same output but expresses each filter as a
     * half-open range on the raw timestamp, which the new orders_created_at_index
     * can seek, and folds the per-day loops into single grouped queries.
     */
    private function buildDashboard(): array
    {
        $dayNames   = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $startOfWeek = now()->startOfWeek();
        $endOfWeek   = $startOfWeek->copy()->addDays(6)->endOfDay();

        // ── Headline totals: two aggregates collapse into one pass ──────────
        $totals = Order::query()
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as sales_total')
            ->first();

        $totalOrders = (int) $totals->order_count;
        $totalSales  = (float) $totals->sales_total;

        // ── Status counts: three COUNT(*) calls collapse into one GROUP BY ──
        $statusRows = Order::query()
            ->select('status', DB::raw('COUNT(*) as status_count'))
            ->groupBy('status')
            ->pluck('status_count', 'status');

        $pendingCount       = (int) ($statusRows['Pending'] ?? 0);
        $orderStatusCounts  = [
            'Pending'     => (int) ($statusRows['Pending'] ?? 0),
            'In-Progress' => (int) ($statusRows['In-Progress'] ?? 0),
            'Completed'   => (int) ($statusRows['Completed'] ?? 0),
        ];

        // ── Today vs yesterday, one query over a two day range ─────────────
        // Date bucketing is still done in SQL, but only to fold the handful of
        // rows inside a narrow, index-seekable range. This matches how
        // whereDate() behaved before, so the bucketing semantics are unchanged.
        // DateBucket picks the expression the live driver understands.
        $startOfToday     = now()->startOfDay();
        $startOfYesterday = $startOfToday->copy()->subDay();

        $recentDays = Order::query()
            ->whereBetween('created_at', [$startOfYesterday, $startOfToday->copy()->endOfDay()])
            ->selectRaw(DateBucket::day('created_at').' as day_key, COALESCE(SUM(total_amount), 0) as day_total')
            ->groupBy('day_key')
            ->pluck('day_total', 'day_key');

        $todaySales     = (float) ($recentDays[$startOfToday->toDateString()] ?? 0);
        $yesterdaySales = (float) ($recentDays[$startOfYesterday->toDateString()] ?? 0);
        $todayPctChange = $yesterdaySales > 0
            ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1)
            : 0;

        // ── Month over month, one query spanning both months ───────────────
        // The old code called whereMonth('created_at', now()->month) with no
        // year constraint, so "this month" also counted the same month number
        // from every previous year and the percentage change was wrong. The
        // range below pins both months explicitly.
        $monthRangeStart = now()->startOfMonth()->subMonth()->startOfMonth();
        $monthRangeEnd   = now()->endOfMonth()->endOfDay();

        $monthlyRows = Order::query()
            ->whereBetween('created_at', [$monthRangeStart, $monthRangeEnd])
            ->selectRaw(
                DateBucket::year('created_at').' as year_key, '.DateBucket::monthNumber('created_at').' as month_key, '
                .'COUNT(*) as month_orders, COALESCE(SUM(total_amount), 0) as month_total'
            )
            ->groupBy('year_key', 'month_key')
            ->get();

        $byMonth = [];
        foreach ($monthlyRows as $row) {
            $byMonth[sprintf('%04d-%02d', $row->year_key, $row->month_key)] = $row;
        }

        $thisMonth      = now();
        $lastMonth      = now()->subMonth();
        $thisMonthKey   = $thisMonth->format('Y-m');
        $lastMonthKey   = $lastMonth->format('Y-m');

        $thisMonthRow = $byMonth[$thisMonthKey] ?? null;
        $lastMonthRow = $byMonth[$lastMonthKey] ?? null;

        $thisMonthOrders = (int) ($thisMonthRow->month_orders ?? 0);
        $lastMonthOrders = (int) ($lastMonthRow->month_orders ?? 0);
        $ordersPctChange = $lastMonthOrders > 0
            ? round((($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : 0;

        $thisMonthSales = (float) ($thisMonthRow->month_total ?? 0);
        $lastMonthSales = (float) ($lastMonthRow->month_total ?? 0);
        $salesPctChange = $lastMonthSales > 0
            ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : 0;

        // ── Weekly sales chart: 14 queries collapse into one ────────────────
        $salesRows = Order::query()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->selectRaw(
                DateBucket::day('created_at').' as day_key, '
                .'COALESCE(SUM(total_amount), 0) as day_total, '
                .'COUNT(*) as day_orders'
            )
            ->groupBy('day_key')
            ->get()
            ->keyBy('day_key');

        $salesDays = [];
        foreach ($dayNames as $i => $dayName) {
            $date = $startOfWeek->copy()->addDays($i);
            $key  = $date->toDateString();

            $salesDays[$dayName] = [
                'amount' => (float) ($salesRows[$key]->day_total ?? 0),
                'orders' => (int) ($salesRows[$key]->day_orders ?? 0),
            ];
        }

        // ── Weekly production chart: 7 whereHas subqueries become one join ──
        // The original ran a correlated EXISTS subquery per weekday, each one
        // re-deriving the same join. A single explicit join over the same
        // seven day range replaces all seven.
        $productionRows = OrderPhaseItem::query()
            ->join('order_phases', 'order_phases.Phase_Id', '=', 'order_phase_items.phase_id')
            ->join('orders', 'orders.Order_Id', '=', 'order_phases.order_id')
            ->whereBetween('orders.created_at', [$startOfWeek, $endOfWeek])
            ->selectRaw(
                DateBucket::day('orders.created_at').' as day_key, '
                .'COALESCE(SUM(order_phase_items.base_qty), 0) as day_qty'
            )
            ->groupBy('day_key')
            ->pluck('day_qty', 'day_key');

        $prodDays = [];
        foreach ($dayNames as $i => $dayName) {
            $date = $startOfWeek->copy()->addDays($i);
            $prodDays[$dayName] = (int) ($productionRows[$date->toDateString()] ?? 0);
        }

        // ── Recent sales list ───────────────────────────────────────────────
        $recentSales = Order::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id'          => $order->order_number,
                    'customer'    => $order->customer_name,
                    'date'        => $order->created_at->diffForHumans(),
                    'amount'      => $order->total_amount,
                    'status'      => $order->status,
                    'statusColor' => match ($order->status) {
                        'Completed'   => 'bg-green-50 text-green-600',
                        'In-Progress' => 'bg-emerald-50 text-emerald-600',
                        default       => 'bg-gray-50 text-gray-500',
                    },
                ];
            })->toArray();

        // ── Top products ───────────────────────────────────────────────────
        // maxSold is the largest base_qty total across *all* products, which
        // is not necessarily among the five highest by revenue, so it needs
        // its own pass. That query groups the whole table and leans on the
        // new opi_name_index to stream instead of buffer.
        $maxSold = (int) (OrderPhaseItem::select(DB::raw('SUM(base_qty) as total'))
            ->groupBy('name')
            ->orderByDesc('total')
            ->limit(1)
            ->value('total') ?? 1);

        $topProducts = OrderPhaseItem::select(
            'name',
            DB::raw('SUM(base_qty) as total_sold'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
            ->groupBy('name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($maxSold) {
                return [
                    'name'    => $item->name,
                    'sold'    => (int) $item->total_sold,
                    'revenue' => (float) $item->total_revenue,
                    'pct'     => round(($item->total_sold / $maxSold) * 100),
                ];
            })->toArray();

        // ── Inventory widgets: two counts collapse into one conditional sum ─
        $inventory = Product::query()
            ->selectRaw(
                'COUNT(*) as item_count, '
                ."COALESCE(SUM(CASE WHEN status IN ('Low Stock', 'Out of Stock') THEN 1 ELSE 0 END), 0) as low_count"
            )
            ->first();

        $inventoryItemCount = (int) $inventory->item_count;
        $lowStockCount      = (int) $inventory->low_count;

        return compact(
            'totalOrders', 'totalSales', 'pendingCount', 'todaySales',
            'ordersPctChange', 'salesPctChange', 'todayPctChange',
            'salesDays', 'prodDays', 'recentSales', 'topProducts', 'orderStatusCounts',
            'inventoryItemCount', 'lowStockCount'
        );
    }

    /**
     * Chart data for the dashboard's period switcher.
     *
     * The three branches each used to issue two queries per bucket, up to 24
     * for the yearly view. Each is now a single grouped query over a range,
     * and every bucket is initialised to zero so the chart renders a full set
     * of labels instead of collapsing to whatever happens to have data.
     */
    public function salesData(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $data   = [];

        if ($period === 'weekly') {
            $start = now()->startOfWeek(Carbon::MONDAY);
            $end   = $start->copy()->addDays(6)->endOfDay();

            $rows = Order::query()
                ->whereBetween('created_at', [$start, $end])
->selectRaw(
                DateBucket::day('created_at').' as day_key, '
                .'COALESCE(SUM(total_amount), 0) as day_total, COUNT(*) as day_orders'
                )
                ->groupBy('day_key')
                ->get();

            $buckets = [];
            foreach ($rows as $row) {
                $buckets[$row->day_key] = $row;
            }

            foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $i => $label) {
                $key = $start->copy()->addDays($i)->toDateString();
                $data[$label] = [
                    'amount' => (float) ($buckets[$key]->day_total ?? 0),
                    'orders' => (int) ($buckets[$key]->day_orders ?? 0),
                ];
            }
        } elseif ($period === 'monthly') {
            $start = now()->startOfMonth();
            $end   = now()->endOfMonth();

            // Buckets here are calendar-week offsets inside the month, not
            // months, so there is no single GROUP BY that produces them. Five
            // range aggregates replace the previous ten queries, and each one
            // is an index seek on orders_created_at_index.
            for ($w = 0; $w < 5; $w++) {
                $wStart = $start->copy()->addWeeks($w);
                if ($wStart->gt($end)) break;

                $slice = Order::query()
                    ->whereBetween('created_at', [
                        $wStart->startOfDay(),
                        $wStart->copy()->addDays(6)->endOfDay(),
                    ])
                    ->selectRaw(
                        'COALESCE(SUM(total_amount), 0) as bucket_total, COUNT(*) as bucket_orders'
                    )
                    ->first();

                $data['Wk ' . ($w + 1)] = [
                    'amount' => (float) ($slice->bucket_total ?? 0),
                    'orders' => (int) ($slice->bucket_orders ?? 0),
                ];
            }
        } elseif ($period === 'yearly') {
            $start = now()->startOfYear();
            $end   = now()->endOfYear();

            $rows = Order::query()
                ->whereBetween('created_at', [$start, $end])
->selectRaw(
                DateBucket::monthNumber('created_at').' as month_key, '
                .'COALESCE(SUM(total_amount), 0) as bucket_total, COUNT(*) as bucket_orders'
                )
                ->groupBy('month_key')
                ->get();

            $buckets = [];
            foreach ($rows as $row) {
                $buckets[(int) $row->month_key] = $row;
            }

            foreach (['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $i => $label) {
                $month = $i + 1;
                $data[$label] = [
                    'amount' => (float) ($buckets[$month]->bucket_total ?? 0),
                    'orders' => (int) ($buckets[$month]->bucket_orders ?? 0),
                ];
            }
        }

        return response()->json($data);
    }
}
