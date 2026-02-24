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
    public function index()
    {
        // === Sales KPIs ===
        $totalRevenue       = Order::sum('total_amount');
        $totalOrders        = Order::count();
        $avgOrderValue      = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $completedThisMonth = Order::whereMonth('created_at', now()->month)->count();

        // Revenue trend (last 12 months)
        $revenueTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->format('M');
            $monthRevenue = Order::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');

            $revenueTrend[$monthLabel] = (float)$monthRevenue;
        }

        // Sales by category
        $salesByCategory = OrderItem::select(
                'inventory_items.category',
                DB::raw('SUM(order_items.subtotal) as total')
            )
            ->join('inventory_items', 'order_items.inventory_item_id', '=', 'inventory_items.id')
            ->groupBy('inventory_items.category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => [
                'category' => $row->category,
                'total'    => (float)$row->total,
            ])->toArray();

        // Top products
        $topProducts = OrderItem::select(
                'order_items.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('order_items.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'name'    => $item->name,
                'sold'    => (int)$item->total_sold,
                'revenue' => (float)$item->total_revenue,
            ])->toArray();

        // Top customers
        $topCustomers = Order::select(
                'customer_name',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_spent')
            )
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

        // Production chart (weekly)
        $prodDays = [];
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $startOfWeek = now()->startOfWeek();
        foreach ($dayNames as $i => $dayName) {
            $date = $startOfWeek->copy()->addDays($i);
            $prodDays[$dayName] = Order::whereDate('created_at', $date)
                ->withSum('items', 'quantity')
                ->get()
                ->sum('items_sum_quantity') ?? 0;
        }

        // Order status distribution
        $orderStatusCounts = [
            'Pending'     => Order::where('status', 'Pending')->count(),
            'In-Progress' => Order::where('status', 'In-Progress')->count(),
            'Completed'   => Order::where('status', 'Completed')->count(),
        ];

        // Worker efficiency (assignment load)
        $employees = \App\Models\User::where('role', 'employee')->get();
        $workerEfficiency = $employees->map(function ($emp) {
            $totalAssignments = $emp->assignments()->count();
            $completedAssignments = $emp->assignments()->where('status', 'completed')->count();
            $load = $totalAssignments > 0 ? round(($completedAssignments / max($totalAssignments, 1)) * 100) : 0;
            return [
                'name'    => $emp->name,
                'initial' => strtoupper(substr($emp->name, 0, 1)),
                'load'    => $load,
            ];
        })->toArray();

        // Unique customer count this month
        $activeCustomers = Order::whereMonth('created_at', now()->month)
            ->distinct('customer_name')
            ->count('customer_name');

        return view('pages.analytics', compact(
            'totalRevenue', 'totalOrders', 'avgOrderValue', 'completedThisMonth',
            'revenueTrend', 'salesByCategory', 'topProducts', 'topCustomers', 'prodDays',
            'orderStatusCounts', 'workerEfficiency', 'activeCustomers'
        ));
    }

    public function reports()
    {
        return $this->index();
    }
}
