<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderPhaseItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Support\DateBucket;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'this_month');

        [$startDate, $endDate] = $this->getPeriodRange($period);

        $totalRevenue       = Order::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
        $totalOrders        = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $avgOrderValue      = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $completedThisMonth = Order::whereBetween('created_at', [$startDate, $endDate])->where('status', 'Completed')->count();

        $activeCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('customer_name')->count('customer_name');

        // 12 months of revenue in one grouped scan, replacing twelve
        // whereYear()+whereMonth() pairs that could not use an index.
        $revenueRows = Order::query()
            ->whereBetween('created_at', [
                now()->subMonths(11)->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->selectRaw(
                DateBucket::month('created_at').' as bucket, '
                .'COALESCE(SUM(total_amount), 0) as month_total'
            )
            ->groupBy('bucket')
            ->get()
            ->keyBy(fn ($row) => (string) $row->bucket);

        $revenueTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenueTrend[$date->format('M')] = (float) ($revenueRows[$date->format('Y-m')]->month_total ?? 0);
        }

        $salesByCategory = OrderPhaseItem::select(
                'products.category',
                DB::raw('SUM(order_phase_items.subtotal) as total')
            )
            ->join('products', 'order_phase_items.product_id', '=', 'products.Product_Id')
            ->join('order_phases', 'order_phase_items.phase_id', '=', 'order_phases.Phase_Id')
            ->join('orders', 'order_phases.order_id', '=', 'orders.Order_Id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => ['category' => $row->category, 'total' => (float)$row->total])
            ->toArray();

        $topProducts = OrderPhaseItem::select(
                'order_phase_items.name',
                DB::raw('SUM(order_phase_items.base_qty) as total_sold'),
                DB::raw('SUM(order_phase_items.subtotal) as total_revenue')
            )
            ->join('order_phases', 'order_phase_items.phase_id', '=', 'order_phases.Phase_Id')
            ->join('orders', 'order_phases.order_id', '=', 'orders.Order_Id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('order_phase_items.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'name'    => $item->name,
                'sold'    => (int)$item->total_sold,
                'revenue' => (float)$item->total_revenue,
            ])->toArray();

        $topCustomers = Order::select(
                'customer_name',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_spent')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('customer_name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'name'    => $c->customer_name,
                'orders'  => $c->order_count,
                'spent'   => (float)$c->total_spent,
                'initial' => strtoupper(substr($c->customer_name, 0, 1)),
            ])->toArray();

        // Seven correlated EXISTS subqueries collapse into one explicit join
        // over the same week-long range.
        $dayNames    = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $startOfWeek = now()->startOfWeek();

        $productionRows = OrderPhaseItem::query()
            ->join('order_phases', 'order_phases.Phase_Id', '=', 'order_phase_items.phase_id')
            ->join('orders', 'orders.Order_Id', '=', 'order_phases.order_id')
            ->whereBetween('orders.created_at', [
                $startOfWeek->copy()->startOfDay(),
                $startOfWeek->copy()->addDays(6)->endOfDay(),
            ])
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

        $orderStatusCounts = [
            'Pending'     => Order::whereBetween('created_at', [$startDate, $endDate])->where('status', 'Pending')->count(),
            'In-Progress' => Order::whereBetween('created_at', [$startDate, $endDate])->where('status', 'In-Progress')->count(),
            'Completed'   => Order::whereBetween('created_at', [$startDate, $endDate])->where('status', 'Completed')->count(),
        ];

        $employees = \App\Models\User::where('role', 'employee')->get();
        $workerEfficiency = $employees->map(function ($emp) {
            $total     = $emp->assignments()->count();
            $completed = $emp->assignments()->where('status', 'completed')->count();
            return [
                'name'    => $emp->name,
                'initial' => strtoupper(substr($emp->name, 0, 1)),
                'load'    => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        })->toArray();

        return view('pages.analytics', compact(
            'totalRevenue', 'totalOrders', 'avgOrderValue', 'completedThisMonth',
            'revenueTrend', 'salesByCategory', 'topProducts', 'topCustomers', 'prodDays',
            'orderStatusCounts', 'workerEfficiency', 'activeCustomers', 'period'
        ));
    }

    public function reports(Request $request)
    {
        return $this->index($request);
    }

    public function exportCsv(Request $request)
    {
        $period = $request->input('period', 'this_month');
        [$startDate, $endDate] = $this->getPeriodRange($period);

        $orders = Order::with('items')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'analytics_report_' . now()->format('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Transaction ID', 'Customer', 'Items', 'Total Amount (PHP)', 'Status', 'Date']);
            foreach ($orders as $o) {
                fputcsv($h, [
                    $o->order_number,
                    $o->customer_name,
                    $o->items->map(fn($i) => $i->name)->implode('; '),
                    number_format($o->total_amount, 2),
                    $o->status,
                    $o->created_at->format('Y-m-d'),
                ]);
            }
            fclose($h);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getPeriodRange(string $period): array
    {
        return match($period) {
            'last_7'     => [now()->subDays(6)->startOfDay(),  now()->endOfDay()],
            'last_30'    => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(),            now()->endOfMonth()],
            'quarter'    => [now()->startOfQuarter(),          now()->endOfQuarter()],
            'this_year'  => [now()->startOfYear(),             now()->endOfYear()],
            default      => [now()->startOfMonth(),            now()->endOfMonth()],
        };
    }
}