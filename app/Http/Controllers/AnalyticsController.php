<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderPhaseItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        $revenueTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel   = $date->format('M');
            $monthRevenue = Order::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
            $revenueTrend[$monthLabel] = (float)$monthRevenue;
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

        $prodDays    = [];
        $dayNames    = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $startOfWeek = now()->startOfWeek();
        foreach ($dayNames as $i => $dayName) {
            $date = $startOfWeek->copy()->addDays($i);
            $prodDays[$dayName] = (int) OrderPhaseItem::whereHas('phase.order', function ($q) use ($date) {
                $q->whereDate('created_at', $date);
            })->sum('base_qty');
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