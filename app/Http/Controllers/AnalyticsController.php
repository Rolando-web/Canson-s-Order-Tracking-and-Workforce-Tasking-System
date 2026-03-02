<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'this_month');

        // === Date range based on period ===
        [$startDate, $endDate] = $this->getPeriodRange($period);

        // === Sales KPIs (scoped to period) ===
        $totalRevenue       = Order::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
        $totalOrders        = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $avgOrderValue      = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $completedThisMonth = Order::whereBetween('created_at', [$startDate, $endDate])->where('status', 'Completed')->count();

        // Active customers in period
        $activeCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('customer_name')->count('customer_name');

        // Revenue trend (12 months always for the chart)
        $revenueTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel   = $date->format('M');
            $monthRevenue = Order::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
            $revenueTrend[$monthLabel] = (float)$monthRevenue;
        }

        // Sales by category (scoped to period)
        $salesByCategory = OrderItem::select(
                'inventory_items.category',
                DB::raw('SUM(order_items.subtotal) as total')
            )
            ->join('inventory_items', 'order_items.inventory_item_id', '=', 'inventory_items.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('inventory_items.category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => ['category' => $row->category, 'total' => (float)$row->total])
            ->toArray();

        // Top products (scoped to period)
        $topProducts = OrderItem::select(
                'order_items.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('order_items.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'name'    => $item->name,
                'sold'    => (int)$item->total_sold,
                'revenue' => (float)$item->total_revenue,
            ])->toArray();

        // Top customers (scoped to period)
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

        // Production chart (this week always)
        $prodDays    = [];
        $dayNames    = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $startOfWeek = now()->startOfWeek();
        foreach ($dayNames as $i => $dayName) {
            $date = $startOfWeek->copy()->addDays($i);
            $prodDays[$dayName] = Order::whereDate('created_at', $date)
                ->withSum('items', 'quantity')->get()->sum('items_sum_quantity') ?? 0;
        }

        // Order status distribution (scoped to period)
        $orderStatusCounts = [
            'Pending'     => Order::whereBetween('created_at', [$startDate, $endDate])->where('status', 'Pending')->count(),
            'In-Progress' => Order::whereBetween('created_at', [$startDate, $endDate])->where('status', 'In-Progress')->count(),
            'Completed'   => Order::whereBetween('created_at', [$startDate, $endDate])->where('status', 'Completed')->count(),
        ];

        // Worker efficiency
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
                    $o->order_id,
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

    // ---------------------------------------------------------------
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
